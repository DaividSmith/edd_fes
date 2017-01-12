<?php
/**
 * FES Forms
 *
 * This file deals with the rendering and saving of FES forms,
 * particularly from shortcodes.
 *
 * @package FES
 * @subpackage Frontend
 * @since 2.0.0
 *
 * @todo At some point we need to introduce
 *       a single fes_form shortcode and
 *       deprecate the other ones.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FES Forms.
 *
 * Register the form shortcodes and create render/save
 * ajax functions for them.
 *
 * @since 2.0.0
 * @access public
 */
class FES_Forms {
	/**
	 * FES Form Actions and Shortcodes.
	 *
	 * Registers ajax endpoints to save FES forms with
	 * on the frontend as well as registers shortcodes for
	 * the default FES forms.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @return void
	 */
	function __construct() {
		add_shortcode( 'fes_submission_form', array( $this, 'render_submission_form' ) );
		add_shortcode( 'fes_profile_form', array( $this, 'render_profile_form' ) );
		add_shortcode( 'fes_login_form', array( $this, 'render_login_form' ) );
		add_shortcode( 'fes_registration_form', array( $this, 'render_registration_form' ) );
		add_shortcode( 'fes_login_registration_form', array( $this, 'render_login_registration_form' ) );
		add_shortcode( 'fes_vendor_contact_form', array( $this, 'contact_form_shortcode' ) );

		// save actions
		add_action( 'wp_ajax_fes_submit_profile_form', array( $this, 'submit_profile_form' ) );
		add_action( 'wp_ajax_nopriv_fes_submit_profile_form', array( $this, 'submit_profile_form' ) );

		add_action( 'wp_ajax_fes_submit_submission_form', array( $this, 'submit_submission_form' ) );
		add_action( 'wp_ajax_nopriv_fes_submit_submission_form', array( $this, 'submit_submission_form' ) );

		add_action( 'wp_ajax_fes_submit_registration_form', array( $this, 'submit_registration_form' ) );
		add_action( 'wp_ajax_nopriv_fes_submit_registration_form', array( $this, 'submit_registration_form' ) );

		add_action( 'wp_ajax_fes_submit_login_form', array( $this, 'submit_login_form' ) );
		add_action( 'wp_ajax_nopriv_fes_submit_login_form', array( $this, 'submit_login_form' ) );

		add_action( 'wp_ajax_fes_submit_vendor_contact_form', array( $this, 'submit_vendor_contact_form' ) );
		add_action( 'wp_ajax_nopriv_fes_submit_vendor_contact_form', array( $this, 'submit_vendor_contact_form' ) );
	}

	/**
	 * Render FES Form.
	 *
	 * Renders an FES form based on the type of form.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @todo  Deprecate in 2.4.
	 *
	 * @param string $type Type of FES form.
	 * @param int $id ID of user/post to edit in form.
	 * @param bool $readonly Whether the form is readonly.
	 * @param array $args Additional arguments to send
	 *                    to form rendering functions.
	 * @return void
	 */
	function render_form( $type = 'submission', $id = false, $readonly = false, $args = array() ) {
		EDD_FES()->setup->enqueue_form_assets();
		switch ( $type ) {
			case 'submission':
				echo $this->render_submission_form( $id, $readonly, $args );
				break;
			case 'profile':
				echo $this->render_profile_form( $id, $readonly, $args );
				break;
			case 'login':
				echo $this->render_login_form( $args );
				break;
			case 'registration':
				echo $this->render_registration_form( $id, $readonly, $args );
				break;
			case 'login-registration':
				echo $this->render_login_registration_form ( $args );
				break;
			case 'vendor-contact-form':
				echo $this->render_vendor_contact_form( $id, $readonly, $args );
				break;
			default:
				echo $this->render_submission_form( $id, $readonly, $args );
				break;
			}
	}

	/**
	 * Render Contact Form Shortcode
	 *
	 * Renders the contact form shortcode.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param string $args Additional arguments to send
	 *                    to form rendering functions.
	 * @param string $content Unused.
	 * @return string HTML of vendor contact form.
	 */
	function contact_form_shortcode( $args, $content = null ) {
		$args = shortcode_atts( array( 'id' => 0, ), $args, 'vendor_contact_form' );
		return $this->render_vendor_contact_form( absint( trim( $args['id'] ) ), false, $args );
	}

	/**
	 * Render Vendor Contact Form.
	 *
	 * Renders vendor contact form.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param int  $user_id ID of user to contact.
	 * @param bool $readonly Whether the form is readonly.
	 * @param array $args Additional arguments to send
	 *                    to form rendering functions.
	 * @return string HTML of vendor contact form.
	 */
	function render_vendor_contact_form( $user_id = 0, $readonly = false, $args = array() ) {

		if ( empty( $user_id ) ) {
			$user = fes_get_vendor();

			if ( $user ) {
				$user_id = $user->ID;
			}
		}

		if ( empty( $user_id ) ) {
			// still no id? One last try. Let's return
			return sprintf( __( 'No %s ID set!', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( true, false ) );
		}


		$vendor = new FES_Vendor( $user_id, true );

		if ( empty( $vendor->id ) ) {
			return sprintf( __( 'No %s found!', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( false, false ) );
		}

		// load the scripts so others don't have to
		EDD_FES()->setup->enqueue_form_assets();

		$form_id = EDD_FES()->helper->get_option( 'fes-vendor-contact-form', false );
		$header = '<h1 class="fes-headers" id="fes-vendor-contact-form-title">'. sprintf( _x( 'Contact %s', 'FES lowercase singular setting for vendor', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( false, false ) ) . '</h1>';

		/**
		 * Vendor Contact Form Header.
		 *
		 * Adjust the header used for the vendor contact
		 * form.
		 *
		 * @since 2.0.0
		 *
		 * @deprecated 2.3.0
		 *
		 * @param  string $header Header to display.
		 */
		$output  = apply_filters( 'fes_vendor_contact_form_header', $header );

		// Make the FES Form
		$form    = EDD_FES()->helper->get_form_by_id( $form_id, $vendor->id );

		$form->save_id = $vendor->id;

		// Render the FES Form
		$output .= $form->render_form_frontend( $vendor->id, $readonly );
		return $output;
	}

	/**
	 * Render Login Form.
	 *
	 * Renders login form.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param int  $user_id Basically unused.
	 * @param bool $readonly Whether the form is readonly.
	 * @param array $args Additional arguments to send
	 *                    to form rendering functions.
	 * @return string HTML of login form.
	 */
	function render_login_form( $user_id = false, $readonly = false, $args = array() ) {
		if ( is_user_logged_in() ) {
			// already logged in? Don't show the login form
			return apply_filters( 'fes_render_login_form_user_logged_in', '' );
		}

		// load the scripts so others don't have to
		EDD_FES()->setup->enqueue_form_assets();

		$form_id = EDD_FES()->helper->get_option( 'fes-login-form', false );
		$output = '';
		$header = '<h1 class="fes-headers" id="fes-login-form-title">'. __( 'Login', 'edd_fes' ) . '</h1>';

		/**
		 * Login Form Header.
		 *
		 * Adjust the header used for the login
		 * form.
		 *
		 * @since 2.0.0
		 *
		 * @deprecated 2.3.0
		 *
		 * @param  string $header Header to display.
		 */
		$output .= apply_filters( 'fes_login_form_header', $header );

		// Make the FES Form
		$form = EDD_FES()->helper->get_form_by_id( $form_id, $user_id );

		// Render the FES Form
		$output .= $form->render_form_frontend( $user_id, $readonly );
		$output .= '<a href="'. wp_lostpassword_url() . '" id="fes_lost_password_link" title="' . __( 'Lost Password?', 'edd_fes' ) . '">' . __( "Lost Password?", "edd_fes" ) . '</a>';
		return $output;
	}

	/**
	 * Render Registration Form.
	 *
	 * Renders registration form.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param int  $user_id User id to make vendor.
	 * @param bool $readonly Whether the form is readonly.
	 * @param array $args Additional arguments to send
	 *                    to form rendering functions.
	 * @return string HTML of registration form.
	 */
	function render_registration_form( $user_id = false, $readonly = false, $args = array()  ) {
		if ( $user_id === -2 || empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		$form_id = EDD_FES()->helper->get_option( 'fes-registration-form', false );

		// load the scripts so others don't have to
		EDD_FES()->setup->enqueue_form_assets();

		$output = '';
		$header = '<h1 class="fes-headers" id="fes-registration-form-title">'. __( 'Register', 'edd_fes' ) . '</h1>';

		/**
		 * Registration Form Header.
		 *
		 * Adjust the header used for the registration
		 * form.
		 *
		 * @since 2.0.0
		 *
		 * @deprecated 2.3.0
		 *
		 * @param  string $header Header to display.
		 */
		$output .= apply_filters( 'fes_registration_form_header', $header );
		// Make the FES Form
		$form = EDD_FES()->helper->get_form_by_id( $form_id, $user_id );

		// Render the FES Form
		$output .= $form->render_form_frontend( $user_id, $readonly );
		return $output;
	}

	/**
	 * Render Registration/Login Combo.
	 *
	 * Renders registration/login combo.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @todo  Rewrite this nonsense to make it more dev friendly.
	 *
	 * @param int  $user_id User id to make vendor.
	 * @param bool $readonly Whether the form is readonly.
	 * @param array $args Additional arguments to send
	 *                    to form rendering functions.
	 * @return string HTML of registration/login combo.
	 */
	function render_login_registration_form( $user_id = false, $readonly = false, $args = array() ) {
		$count = EDD_FES()->vendors->combo_form_count();
		$width = $count == 2 ? '44%' : '100%';
		$output = '';

		if ( $count == 2 ) {
			$output .= '<table class="fes-login-registration fes-login-registration-combo"><tr><td id="fes_login_registration_form_row_left" class="fes_login_registration_form_row fes_login_registration_form_row_half_width" style="width: 44%; clear: none; float: left;">';
			$output .=  $this->render_login_form( $user_id, $readonly, $args );
			$output .=  '</td><td id="fes_login_registration_form_row_right" class="fes_login_registration_form_row fes_login_registration_form_row_half_width" style="width: 44%; float: right;">';
			$output .=  $this->render_registration_form( $user_id, $readonly, $args );
			$output .=  '</td></tr></div></table>';
		} else if ( $count === 1 ) {
			if ( EDD_FES()->vendors->can_see_login() ) {
				$output .=  '<table class="fes-login-registration fes-login-registration-login"><tr><td id="fes_login_registration_form_row_full_width_login" class="fes_login_registration_form_row" style="width: 88%; clear: none; float: left;">';
				$output .=  $this->render_login_form( $user_id, $readonly, $args );
				$output .=  '</td></tr></div></table>';
			} else if ( EDD_FES()->vendors->can_see_registration() ) {
				$output .=  '<table class="fes-login-registration fes-login-registration-register"><tr><td id="fes_login_registration_form_row_full_width_registration" class="fes_login_registration_form_row" style="width: 88%; clear: none; float: left;">';
				if ( EDD_FES()->helper->get_option( 'fes-allow-registrations', false ) || EDD_FES()->helper->get_option( 'fes-allow-applications', false ) ) {
					$output .=  $this->render_registration_form( $user_id, $readonly, $args );
				} else {
					$output .= __( 'Registration and applications are currently closed', 'edd_fes' );
				}
				$output .=  '</td></tr></div></table>';
			} else if ( EDD_FES()->vendors->user_is_status( 'pending' ) ) {
				$output .=  '<table class="fes-login-registration fes-login-registration-pending"><tr><td id="fes_login_registration_form_row_full_width_pending" class="fes_login_registration_form_row" style="width: 88%; clear: none; float: left;">';
				$output .= __( 'Your application is pending', 'edd_fes' );
				$output .=  '</td></tr></div></table>';
			} else {
				$output .= __( 'An error occured. FES error: CFCF 1', 'edd_fes' );
			}
		} else {
			if ( EDD_FES()->vendors->user_is_status( 'pending' ) ) {
				$output .=  __( 'Your application is pending', 'edd_fes' );
			} else if ( ! EDD_FES()->vendors->user_is_status( 'suspended' ) && ! EDD_FES()->vendors->user_is_status( 'approved' ) && ! EDD_FES()->vendors->can_see_registration() && ! EDD_FES()->vendors->can_see_login() ) {
				$output .= __( 'Applications are currently closed', 'edd_fes' );
			} else if ( ! EDD_FES()->vendors->user_is_status( 'suspended' ) && ! EDD_FES()->vendors->user_is_status( 'approved' ) && ! EDD_FES()->vendors->can_see_registration() && EDD_FES()->vendors->can_see_login() ) {
				$output .= __( 'Registrations are currently closed', 'edd_fes' );
			} else {
				$output .= sprintf( __( 'You are already a %s, go to the %s Dashboard', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( false, false ), EDD_FES()->helper->get_vendor_constant_name( false, true ) );
			}
		}
		return $output;
	}

	/**
	 * Render Submission Form.
	 *
	 * Renders submission form.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @global WP_Post $post Post object of vendor dashboard page.
	 *
	 * @param int  $post_id Post id to edit.
	 * @param bool $readonly Whether the form is readonly.
	 * @param array $args Additional arguments to send
	 *                    to form rendering functions.
	 * @return string HTML of submission form.
	 */
	function render_submission_form( $post_id = false, $readonly = false, $args = array() ) {
		global $post;
		$post_id = isset( $_REQUEST['post_id'] ) && absint( $_REQUEST['post_id'] )  ? absint( $_REQUEST['post_id'] ) : -2;
		$user_id =  get_current_user_id();
		$form_id = EDD_FES()->helper->get_option( 'fes-submission-form', false );

		// Session set for upload watermarking
		$fes_post_id = !empty( $post ) && is_object( $post ) && isset( $post->ID ) ? $post->ID : -2;
		EDD()->session->set( 'fes_dashboard_post_id', $fes_post_id );
		EDD()->session->set( 'fes_post_id', $post_id );
		EDD()->session->set( 'fes_form_id', $form_id );
		EDD()->session->set( 'fes_user_id', $user_id );

		// load the scripts so others don't have to
		EDD_FES()->setup->enqueue_form_assets();

		$output = '';

		// Make the FES Form
		$form = EDD_FES()->helper->get_form_by_id( $form_id, $post_id );

		// Render the FES Form
		if ( $post_id && $post_id !== -2 ) {
			$output .= '<h1 class="fes-headers" id="fes-edit-product-form-title" >'.__( 'Edit', 'edd_fes' ) . ' ' . EDD_FES()->helper->get_product_constant_name( $plural = false, $uppercase = true ) . __( ': #', 'edd_fes' ).$post_id.'</h1>';
		} else {
			$output .= '<h1 class="fes-headers" id="fes-new-product-form-title" >'.__( 'Create New', 'edd_fes' ) . ' ' . EDD_FES()->helper->get_product_constant_name( $plural = false, $uppercase = true ) .'</h1>';
		}
		$output  = apply_filters( 'fes_submission_form_header', $output );
		$output .= $form->render_form_frontend( $user_id, $readonly );
		return $output;
	}


	/**
	 * Render Profile Form.
	 *
	 * Renders profile form.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param int  $post_id User id to edit.
	 * @param bool $readonly Whether the form is readonly.
	 * @param array $args Additional arguments to send
	 *                    to form rendering functions.
	 * @return string HTML of profile form.
	 */
	function render_profile_form( $user_id = -2, $readonly = false, $args = array() ) {
		if ( $user_id === -2 || empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		$form_id = EDD_FES()->helper->get_option( 'fes-profile-form', false );

		// load the scripts so others don't have to
		EDD_FES()->setup->enqueue_form_assets();

		$output = '';

		// Make the FES Form
		$form = EDD_FES()->helper->get_form_by_id( $form_id, $user_id );

		// Render the FES Form
		$header = '<h1 class="fes-headers" id="fes-profile-form-title">'. __( 'Profile', 'edd_fes' ) . '</h1>';

		/**
		 * Profile Form Header.
		 *
		 * Adjust the header used for the profile
		 * form.
		 *
		 * @since 2.0.0
		 *
		 * @deprecated 2.3.0
		 *
		 * @param  string $header Header to display.
		 */
		$output .= apply_filters( 'fes_profile_form_header', $header );

		$output .= $form->render_form_frontend( $user_id, $readonly );
		return $output;
	}

	/**
	 * Submit Form.
	 *
	 * Submit FES form by type of form.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param string  $type Type of FES Form.
	 * @param int  $id User/post id to edit.
	 * @param array $values Values to save.
	 * @param array $args Additional arguments to send
	 *                    to form rendering functions.
	 * @return void
	 */
	function submit_form( $type = 'submission', $id = false, $values = array(), $args = array() ) {
		switch ( $type ) {
			case 'submission':
				$this->submit_submission_form( $id, $values, $args );
				break;
			case 'profile':
				$this->submit_profile_form( $id, $values, $args );
				break;
			case 'login':
				$this->submit_login_form( $id, $values, $args );
				break;
			case 'registration':
				$this->submit_registration_form( $id, $values, $args );
				break;
			case 'vendor_contact':
				$this->submit_vendor_contact_form( $id, $values, $args );
				break;
			default:
				$this->submit_submission_form( $id, $readonly, $args );
				break;
		}

	}

	/**
	 * Submit Vendor Contact Form.
	 *
	 * Submit vendor contact FES form.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param int  $id User id to edit.
	 * @param array $values Values to save.
	 * @param array $args Additional arguments to send
	 *                    to form rendering functions.
	 * @return void
	 */
	function submit_vendor_contact_form( $id = 0, $values = array(), $args = array() ) {
		$form_id   = !empty( $values ) && isset( $values['form_id'] )   ? absint( $values['form_id'] )   : ( isset( $_REQUEST['form_id'] )   ? absint( $_REQUEST['form_id'] )   : EDD_FES()->helper->get_option( 'fes-vendor-contact-form', false ) );
		$user_id   = !empty( $values ) && isset( $values['user_id'] )   ? absint( $values['user_id'] )   : ( isset( $_REQUEST['user_id'] )   ? absint( $_REQUEST['user_id'] )   : get_current_user_id() );
		$vendor_id = !empty( $values ) && isset( $values['vendor_id'] ) ? absint( $values['vendor_id'] ) : ( isset( $_REQUEST['vendor_id'] ) ? absint( $_REQUEST['vendor_id'] ) : -2 );

		$values    = !empty( $values ) ? $values : $_POST;
		// Make the FES Form
		$form      = new FES_Vendor_Contact_Form( $form_id, 'id', $vendor_id );
		// Save the FES Form
		$form->save_form_frontend( $values, $user_id );
	}

	/**
	 * Submit Login Form.
	 *
	 * Submit login FES form.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param int  $id Basically unused.
	 * @param array $values Values to save.
	 * @param array $args Additional arguments to send
	 *                    to form rendering functions.
	 * @return void
	 */
	function submit_login_form( $id = 0, $values = array(), $args = array() ) {
		$form_id   = !empty( $values ) && isset( $values['form_id'] )   ? absint( $values['form_id'] )   : ( isset( $_REQUEST['form_id'] )   ? absint( $_REQUEST['form_id'] )   : EDD_FES()->helper->get_option( 'fes-login-form', false ) );
		$user_id   = !empty( $values ) && isset( $values['user_id'] )   ? absint( $values['user_id'] )   : ( isset( $_REQUEST['user_id'] )   ? absint( $_REQUEST['user_id'] )   : get_current_user_id() );
		$vendor_id = !empty( $values ) && isset( $values['vendor_id'] ) ? absint( $values['vendor_id'] ) : ( isset( $_REQUEST['vendor_id'] ) ? absint( $_REQUEST['vendor_id'] ) : -2 );

		$values    = !empty( $values ) ? $values : $_POST;
		// Make the FES Form
		$form      = new FES_Login_Form( $form_id, 'id', $user_id );

		// Save the FES Form
		$form->save_form_frontend( $values, $user_id );
	}

	/**
	 * Submit Registration Form.
	 *
	 * Submit registration FES form.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param int  $id User id to edit.
	 * @param array $values Values to save.
	 * @param array $args Additional arguments to send
	 *                    to form rendering functions.
	 * @return void
	 */
	function submit_registration_form( $id = 0, $values = array(), $args = array() ) {
		$form_id   = !empty( $values ) && isset( $values['form_id'] )   ? absint( $values['form_id'] )   : ( isset( $_REQUEST['form_id'] )   ? absint( $_REQUEST['form_id'] )   : EDD_FES()->helper->get_option( 'fes-login-form', false ) );
		$user_id   = !empty( $values ) && isset( $values['user_id'] )   ? absint( $values['user_id'] )   : ( isset( $_REQUEST['user_id'] )   ? absint( $_REQUEST['user_id'] )   : get_current_user_id() );
		$vendor_id = !empty( $values ) && isset( $values['vendor_id'] ) ? absint( $values['vendor_id'] ) : ( isset( $_REQUEST['vendor_id'] ) ? absint( $_REQUEST['vendor_id'] ) : -2 );

		$values    = !empty( $values ) ? $values : $_POST;
		// Make the FES Form
		$form      = new FES_Registration_Form( $form_id, 'id', $vendor_id );

		// Save the FES Form
		$form->save_form_frontend( $values, $user_id );
	}

	/**
	 * Submit Submission Form.
	 *
	 * Submit submission FES form.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param int  $id Post id to edit.
	 * @param array $values Values to save.
	 * @param array $args Additional arguments to send
	 *                    to form rendering functions.
	 * @return void
	 */
	function submit_submission_form( $id = 0, $values = array(), $args = array() ) {
		$form_id   = !empty( $values ) && isset( $values['form_id'] )   ? absint( $values['form_id'] )   : ( isset( $_REQUEST['form_id'] )   ? absint( $_REQUEST['form_id'] )   : EDD_FES()->helper->get_option( 'fes-submission-form', false ) );
		$user_id   = !empty( $values ) && isset( $values['user_id'] )   ? absint( $values['user_id'] )   : ( isset( $_REQUEST['user_id'] )   ? absint( $_REQUEST['user_id'] )   : get_current_user_id() );
		$vendor_id = !empty( $values ) && isset( $values['vendor_id'] ) ? absint( $values['vendor_id'] ) : ( isset( $_REQUEST['vendor_id'] ) ? absint( $_REQUEST['vendor_id'] ) : -2 );
		$values    = !empty( $values ) ? $values : $_POST;
		$post_id   = !empty( $values ) && isset( $values['post_id'] ) && $values['post_id'] > 0 ? absint( $values['post_id'] ) : EDD()->session->get( 'fes_post_id' );

		// Make the FES Form
		$form      = new FES_Submission_Form( $form_id, 'id', $post_id );

		// Save the FES Form
		$form->save_form_frontend( $values , $user_id );
	}

	/**
	 * Submit Profile Form.
	 *
	 * Submit profile FES form.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param int  $id User id to edit.
	 * @param array $values Values to save.
	 * @param array $args Additional arguments to send
	 *                    to form rendering functions.
	 * @return void
	 */
	function submit_profile_form( $id = 0, $values = array(), $args = array() ) {
		$form_id   = !empty( $values ) && isset( $values['form_id'] )   ? absint( $values['form_id'] )     : ( isset( $_REQUEST['form_id'] )   ? absint( $_REQUEST['form_id'] )   : EDD_FES()->helper->get_option( 'fes-profile-form', false ) );
		$user_id   = !empty( $values ) && isset( $values['user_id'] )   ? absint( $values['user_id'] )     : ( isset( $_REQUEST['user_id'] )   ? absint( $_REQUEST['user_id'] )   : get_current_user_id() );
		$vendor_id = !empty( $values ) && isset( $values['vendor_id'] ) ? absint( $values['vendor_id'] )   : ( isset( $_REQUEST['vendor_id'] ) ? absint( $_REQUEST['vendor_id'] ) : -2 );
		$values    = !empty( $values ) ? $values : $_POST;

		// Make the FES Form
		$form      = new FES_Profile_Form( $form_id, 'id', $vendor_id );

		// Save the FES Form
		$form->save_form_frontend( $values , $user_id );

	}

	// start deprecated functions
	/**
	 * @ignore This deprecated function will be removed in 2.4.
	 */
	public function required_mark( $readonly = false ) {
		_fes_deprecated_function( 'EDD_FES()->formbuilder_templates->required_mark', '2.3', 'FES_Field->required_mark' );
		if ( $this->required() && !$readonly ) {
			return apply_filters( 'fes_required_mark', '<span class="edd-required-indicator">*</span>' );
		}
	}

	/**
	 * @ignore This deprecated function will be removed in 2.4.
	 */
	public function required_html5( $readonly = false ) {
		_fes_deprecated_function( 'EDD_FES()->formbuilder_templates->required_html5', '2.3', 'FES_Field->required_html5' );
		if ( $this->required() && !$readonly ) {
			echo apply_filters( 'fes_required_html5', ' required="required"' );
		}
	}

	/**
	 * @ignore This deprecated function will be removed in 2.4.
	 */
	public function required_class( $readonly = false ) {
		_fes_deprecated_function( 'EDD_FES()->formbuilder_templates->required_class', '2.3', 'FES_Field->required_class' );
		if ( $this->required() && !$readonly  ) {
			echo apply_filters( 'fes_required_class', ' edd-required-indicator' );
		}
	}

	/**
	 * @ignore This deprecated function will be removed in 2.4.
	 */
	public function label( $attr, $post_id = 0 ) {
		_fes_deprecated_function( 'EDD_FES()->formbuilder_templates->label', '2.3', 'FES_Field->label' );
		$name  = $this->name();
		$label = $this->label();
		ob_start(); ?>
		<div class="fes-label">
			<label for="fes-<?php echo isset( $name ) ? $name : 'cls'; ?>"><?php echo $label. $this->required_mark( $attr ); ?></label>
			<br />
			<?php if ( $this->help() ) : ?>
			<span class="fes-help"><?php echo $this->help(); ?></span>
		  <?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * @ignore This deprecated function will be removed in 2.4.
	 */
	public function required() {
		_fes_deprecated_function( 'EDD_FES()->formbuilder_templates->required', '2.3', 'FES_Field->required' );
		return (bool) $this->characteristics['required'];
	}

	/**
	 * @ignore This deprecated function will be removed in 2.4.
	 */
	public function help() {
		_fes_deprecated_function( 'EDD_FES()->formbuilder_templates->help', '2.3', 'FES_Field->help' );
		return $this->characteristics['help'];
	}

	/**
	 * @ignore This deprecated function will be removed in 2.4.
	 */
	public function name() {
		_fes_deprecated_function( 'EDD_FES()->formbuilder_templates->name()', '2.3', 'FES_Field->name()' );
		return $this->characteristics['name'];
	}

	/**
	 * @ignore This deprecated function will be removed in 2.4.
	 */
	public function placeholder() {
		_fes_deprecated_function( 'EDD_FES()->formbuilder_templates->placeholder', '2.3', 'FES_Field->placeholder' );
		return $this->characteristics['placeholder'];
	}

	/**
	 * @ignore This deprecated function will be removed in 2.4.
	 */
	public function size() {
		_fes_deprecated_function( 'EDD_FES()->formbuilder_templates->size', '2.3', 'FES_Field->size' );
		return $this->characteristics['size'];
	}

	/**
	 * @ignore This deprecated function will be removed in 2.4.
	 */
	public function is_meta( $attr ) {
		_fes_deprecated_function( 'EDD_FES()->formbuilder_templates->is_meta', '2.3', 'FES_Field->is_meta' );
		if ( ( isset( $this->supports['is_meta'] ) && (bool) $this->supports['is_meta'] ) || (  ! isset( $this->supports['is_meta'] ) && isset( $this->characteristics['is_meta'] ) && (bool) $this->characteristics['is_meta'] ) ) {
			return true;
		} else {
			return false;
		}
	}

}
