<?php
/**
 * Schema
 *
 * Contains the default fields for each form
 * as well as schema correction and other helper
 * functions.
 *
 * @package FES
 * @subpackage Schema
 * @since 2.3.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { 
	exit;
}

/**
 * Default Profile Form Fields.
 *
 * The default fields used on the profile
 * form. This function is used to retrieve those
 * fields so they can be used in things like creating
 * the forms for the first time, as well as resetting
 * them.
 * 
 * @since 2.3.0
 * @access public
 *
 * @return array Default fields.
 */
function fes_get_default_profile_form_fields(){
	$fields = array(
		1 => array(
			'template' => 'text',
			'required' => 'yes',
			'label' => __( 'Name of Store', 'edd_fes' ),
			'name' => 'name_of_store',
			'help' => __( 'What would you like your store to be called?', 'edd_fes' ),
			'css' => '',
			'placeholder' => '',
			'default' => '',
			'size' => '40',
			'public' => false,
		),
		2 => array(
			'template' => 'email',
			'required' => 'yes',
			'label' => __( 'Email to use for Contact Form', 'edd_fes' ),
			'name' => 'email_to_use_for_contact_form',
			'help' => __( 'This email, if filled in, will be used for the vendor contact forms. If it is not filled in, the one from your user profile will be used.', 'edd_fes' ),
			'css' => '',
			'placeholder' => '',
			'default' => '',
			'size' => '40',
			'public' => false,
		)
	);
	return $fields;
}

/**
 * Default Submission Form Fields.
 *
 * The default fields used on the submission
 * form. This function is used to retrieve those
 * fields so they can be used in things like creating
 * the forms for the first time, as well as resetting
 * them.
 * 
 * @since 2.3.0
 * @access public
 *
 * @return array Default fields.
 */
function fes_get_default_submission_form_fields(){
	$fields = array(
		1 => array(
			'template'    => 'post_title',
			'required'    => 'yes',
			'label'    => __( 'Download Title', 'edd_fes' ),
			'name'     => 'post_title',
			'help'     => '',
			'css'    => '',
			'placeholder' => '',
			'default'    => '',
			'size'     => '40',
			'public' => false,
		),
		2 => array(
			'template'     => 'post_content',
			'required'     => 'yes',
			'label'     => __( 'Download Body', 'edd_fes' ),
			'name'      => 'post_content',
			'help'      => '',
			'css'      => '',
			'placeholder'  => '',
			'default'     => '',
			'rows'      => '40',
			'cols'      => '25',
			'default'     => '',
			'rich'     => 'no',
			'insert_image' => 'yes',
			'public' => false,
		)
	);
	return $fields;
}

/**
 * Default Registration Form Fields.
 *
 * The default fields used on the registration
 * form. This function is used to retrieve those
 * fields so they can be used in things like creating
 * the forms for the first time, as well as resetting
 * them.
 * 
 * @since 2.3.0
 * @access public
 *
 * @return array Default fields.
 */
function fes_get_default_registration_form_fields(){
	$fields = array(
		1 => array(
			'template' => 'first_name',
			'required' => 'yes',
			'label' => __( 'First Name', 'edd_fes' ),
			'name' => 'first_name',
			'help' => '',
			'css' => '',
			'placeholder' => '',
			'default' => '',
			'size' => '40',
			'public' => false,
		),
		2 => array(
			'template' => 'last_name',
			'required' => 'yes',
			'label' => __( 'Last Name', 'edd_fes' ),
			'name' => 'last_name',
			'help' => '',
			'css' => '',
			'placeholder' => '',
			'default' => '',
			'size' => '40',
			'public' => false,
		),
		3 => array(
			'template' => 'user_email',
			'required' => 'yes',
			'label' => __( 'User Email', 'edd_fes' ),
			'name' => 'user_email',
			'help' => '',
			'css' => '',
			'placeholder' => '',
			'default' => '',
			'size' => '40',
			'public' => false,
		),
		4 => array(
			'template' => 'user_login',
			'required' => 'yes',
			'label' => __( 'Username', 'edd_fes' ),
			'name' => 'user_login',
			'help' => '',
			'css' => '',
			'placeholder' => '',
			'default' => '',
			'size' => '40',
			'public' => false,
		),
		5 => array(
			'template' => 'password',
			'required' => 'yes',
			'label' => __( 'Password', 'edd_fes' ),
			'name' => 'user_pass',
			'help' => '',
			'css' => '',
			'placeholder' => '',
			'default' => '',
			'size' => '40',
			'min_length' => '6',
			'repeat_pass' => 'no',
			'public' => false,
		),
		6 => array(
			'template' => 'display_name',
			'required' => 'yes',
			'label' => __( 'Display Name', 'edd_fes' ),
			'name' => 'display_name',
			'help' => '',
			'css' => '',
			'placeholder' => '',
			'default' => '',
			'size' => '40',
			'public' => false,
		),
	);
	return $fields;
}

/**
 * Default Vendor Contact Form Fields.
 *
 * The default fields used on the vendor contact
 * form. This function is used to retrieve those
 * fields so they can be used in things like creating
 * the forms for the first time, as well as resetting
 * them.
 * 
 * @since 2.3.0
 * @access public
 *
 * @return array Default fields.
 */
function fes_get_default_vendor_contact_form_fields(){
	$fields = array(
		1 => array(
			'template' => 'name',
			'required' => 'yes',
			'label' => __( 'Name', 'edd_fes' ),
			'name' => 'name',
			'help' => '',
			'css' => '',
			'placeholder' => '',
			'default' => '',
			'size' => '40',
			'public' => false,
		),
		2 => array(
			'template' => 'email',
			'required' => 'yes',
			'label' => __( 'Email', 'edd_fes' ),
			'name' => 'email',
			'help' => '',
			'css' => '',
			'placeholder' => '',
			'default' => '',
			'size' => '40',
			'public' => false,
		),
		3 => array(
			'template' => 'textarea',
			'required' => 'yes',
			'label' => __( 'Message', 'edd_fes' ),
			'name' => 'message',
			'help' => '',
			'css' => '',
			'placeholder' => '',
			'default' => '',
			'size' => '40',
			'cols'        => '50',
			'rows'        => '8',			
			'public' => false,
			'rich' => ''
		),
		4 => array(
			'name' => 'recaptcha',
			'template' => 'recaptcha',
			'required' => 'yes',
			'label' => __( 'reCAPTCHA', 'edd_fes' ),
			'html'  => '',
			'help' => '',
			'css' => '',
			'public' => false,
		),
	);
	return $fields;
}

/**
 * Default Login Form Fields.
 *
 * The default fields used on the login
 * form. This function is used to retrieve those
 * fields so they can be used in things like creating
 * the forms for the first time, as well as resetting
 * them.
 * 
 * @since 2.3.0
 * @access public
 *
 * @return array Default fields.
 */
function fes_get_default_login_form_fields(){
	$fields = array(
		1 => array(
			'template' => 'user_login',
			'required' => 'yes',
			'label' => __( 'Username', 'edd_fes' ),
			'name' => 'user_login',
			'size' => 40,
			'help' => '',
			'css' => '',
			'placeholder' => '',
			'default' => '',
			'public' => false,
		),
		2 => array(
			'template' => 'password',
			'required' => 'yes',
			'label' => __( 'Password', 'edd_fes' ),
			'name' => 'user_pass',
			'size' => '40',
			'min_length' => '0',
			'repeat_pass' => 'no',
			'help' => '',
			'css' => '',
			'placeholder' => '',
			'default' => '',
			'public' => false,
		),
		3 => array(
			'name' => 'recaptcha',
			'template' => 'recaptcha',
			'required' => 'yes',
			'label' => __( 'reCAPTCHA', 'edd_fes' ),
			'html'  => '',
			'help' => '',
			'css' => '',
			'public' => false,
		),
	);
	return $fields;
}

/**
 * Save Initial Profile Form.
 *
 * Saves meta for the profile form, as well as optionally
 * saves/resets the default fields.
 * 
 * @since 2.3.0
 * @access public
 *
 * @param int $post_id Post id of the FES form.
 * @param bool $reset_fields Whether to reset fields.
 * @return void
 */
function fes_save_initial_profile_form( $post_id = -2, $reset_fields = true ){
	if ( $post_id === -2 ){
		return false;
	}

	if ( $reset_fields ){
		$fields = fes_get_default_profile_form_fields();
		update_post_meta( $post_id, 'fes-form', $fields );
	}

	update_post_meta( $post_id, 'fes-form-name', 'profile' );
	update_post_meta( $post_id, 'fes-form-type', 'user' );
	update_post_meta( $post_id, 'fes-form-class', 'FES_Profile_Form' );
}

/**
 * Save Initial Registration Form.
 *
 * Saves meta for the registartion form, as well as optionally
 * saves/resets the default fields.
 * 
 * @since 2.3.0
 * @access public
 *
 * @param int $post_id Post id of the FES form.
 * @param bool $reset_fields Whether to reset fields.
 * @return void
 */
function fes_save_initial_registration_form( $post_id = -2, $reset_fields = true ){
	if ( $post_id === -2 ){
		return false;
	}
	
	if ( $reset_fields ){
		$fields = fes_get_default_registration_form_fields();
		update_post_meta( $post_id, 'fes-form', $fields );
	}
	update_post_meta( $post_id, 'fes-form-name', 'registration' );
	update_post_meta( $post_id, 'fes-form-type', 'user' );
	update_post_meta( $post_id, 'fes-form-class', 'FES_Registration_Form' );
}

/**
 * Save Initial Submission Form.
 *
 * Saves meta for the submission form, as well as optionally
 * saves/resets the default fields.
 * 
 * @since 2.3.0
 * @access public
 *
 * @param int $post_id Post id of the FES form.
 * @param bool $reset_fields Whether to reset fields.
 * @return void
 */
function fes_save_initial_submission_form( $post_id = -2, $reset_fields = true ){
	if ( $post_id === -2 ){
		return false;
	}
	
	if ( $reset_fields ){
		$fields = fes_get_default_submission_form_fields();
		update_post_meta( $post_id, 'fes-form', $fields );
	}

	update_post_meta( $post_id, 'fes-form-name', 'submission' );
	update_post_meta( $post_id, 'fes-form-type', 'post' );
	update_post_meta( $post_id, 'fes-form-class', 'FES_Submission_Form' );	
}

/**
 * Save Initial Vendor Contact Form.
 *
 * Saves meta for the vendor contact form, as well as optionally
 * saves/resets the default fields.
 * 
 * @since 2.3.0
 * @access public
 *
 * @param int $post_id Post id of the FES form.
 * @param bool $reset_fields Whether to reset fields.
 * @return void
 */
function fes_save_initial_vendor_contact_form( $post_id = -2, $reset_fields = true ){
	if ( $post_id === -2 ){
		return false;
	}
	
	if ( $reset_fields ){
		$fields = fes_get_default_vendor_contact_form_fields();
		update_post_meta( $post_id, 'fes-form', $fields );
	}

	update_post_meta( $post_id, 'fes-form-name', 'vendor-contact' );
	update_post_meta( $post_id, 'fes-form-type', 'custom' );
	update_post_meta( $post_id, 'fes-form-class', 'FES_Vendor_Contact_Form' );
}

/**
 * Save Initial Login Form.
 *
 * Saves meta for the login form, as well as optionally
 * saves/resets the default fields.
 * 
 * @since 2.3.0
 * @access public
 *
 * @param int $post_id Post id of the FES form.
 * @param bool $reset_fields Whether to reset fields.
 * @return void
 */
function fes_save_initial_login_form( $post_id = -2, $reset_fields = true ){
	if ( $post_id === -2 ){
		return false;
	}
	
	if ( $reset_fields ){
		$fields = fes_get_default_login_form_fields();
		update_post_meta( $post_id, 'fes-form', $fields );
	}

	update_post_meta( $post_id, 'fes-form-name', 'login' );
	update_post_meta( $post_id, 'fes-form-type', 'custom' );
	update_post_meta( $post_id, 'fes-form-class', 'FES_Login_Form' );
}

/**
 * Schema Correction.
 *
 * Attempts to correct all mistakes (and also
 * runs all version upgrade routines that
 * need to change saved characteristics of 
 * a field). If this function has a bug, the 
 * results can be catastrophic. *crosses fingers*
 * 
 * @since 2.3.0
 * @access public
 *
 * @param array $field Field characteristics.
 * @return array Field characteristics to save.
 */
function fes_upgrade_field( $field ) {
	// if there's no template, set it as the input_type
	if ( !isset( $field['template'] ) && isset( $field['input_type'] ) ) {
		$field['template'] = $field['input_type'];
	}

	// some password fields were inserted without a name attribute in 2.2. Whoops. Set to default.
	if ( !isset( $field['name'] ) && $field['template'] == 'password' ) {
		$field['name'] = 'user_pass';
	}

	// and other password fields have the wrong property name
	if ( isset( $field['name'] ) && isset( $field['template'] )  && $field['template'] == 'password' &&  $field['name'] == 'password' ) {
		$field['name'] = 'user_pass';
	}

	// and post excerpts sometimes didn't have a name
	if ( empty( $field['name'] ) && isset( $field['template'] )  && $field['template'] == 'post_excerpt' ) {
		$field['name'] = 'post_excerpt';
	}	

	// if its recaptcha, set the name to recaptcha
	if ( !isset( $field['name'] ) && isset( $field['template'] ) && $field['template'] == 'recaptcha' ) {
		$field['name'] = 'recaptcha';
	}

	// action hooks used the label field as their name. That is incredibly dumb and problematic. Let's fix it.
	if ( isset( $field['template'] ) && $field['template'] == 'action_hook' && isset( $field['label'] ) && !isset( $field['name'] ) ) {
		$field['name'] = $field['label'];
	}

	// split taxonomy fields
	if ( isset( $field['name'] ) && isset( $field['template'] ) && $field['template'] == 'taxonomy' ) {
		if ( $field['name'] == 'download_category' ) {
			$field['template'] = 'download_category';
		} else if (  $field['name'] == 'download_category' ) {
				$field['template'] = 'download_tag';
			}
	}

	// Prettify the template names of our fields (and back convert to template, if did template = input_type above)
	switch ( $field['template'] ) {
		case 'checkbox_field':
			$field['template'] = 'checkbox';
			break;

		case 'fes_honeypot':
			$field['template'] = 'honeypot';
			break;

		case 'custom_hidden_field':
			$field['template'] = 'hidden';
			break;

		case 'radio_field':
			$field['template'] = 'radio';
			break;

		case 'textarea_field':
			$field['template'] = 'textarea';
			break;

		case 'text_field':
			$field['template'] = 'text';
			break;

		case 'website_url':
			$field['template'] = 'url';
			break;

		case 'avatar':
			$field['template'] = 'user_avatar';
			break;

		case 'description':
			$field['template'] = 'user_bio';
			break;

		case 'custom_html':
			$field['template'] = 'html';
			break;

		case 'repeat_field':
			$field['template'] = 'repeat';
			break;

		case 'custom_select':
			$field['template'] = 'select';
			break;

		case 'dropdown_field':
			$field['template'] = 'select';
			break;			

		case 'multiple_select':
			$field['template'] = 'multiselect';
			break;

		case 'date_field':
			$field['template'] = 'date';
			break;

		case 'image_upload':
			$field['template'] = 'featured_image';
			break;

		case 'email_address':
			$field['template'] = 'email';
			break;

		default:
			break;
	}


	// if there's still no name, and it's not meta, grab it from the template
	if ( !isset( $field['name'] ) && isset( $field['template'] ) ) {
		$field['name'] = $field['template'];
	}

	// get rid of this key. We don't use it anywhere.
	// Only serves to confuse
	if ( isset( $field['input_type'] ) ) {
		unset( $field['input_type'] );
	}

	// If there's no name, which is nearly impossible, they'll be in trouble,
	// but at least we can prevent immediate fatal errors
	if ( !isset( $field['name'] ) ) {
		$field['name'] = 'custom_' . time();
	} else {
		 // automatically remove special characters from the meta key
		$field['name'] = sanitize_key( $field['name'] );
	}

	if ( isset( $field['is_meta'] ) && $field['is_meta'] === 'no' ){
		$field['is_meta'] = false;
	}

	if ( isset( $field['is_meta'] ) && $field['is_meta'] === 'yes' ){
		$field['is_meta'] = true;
	}
	
	// In FES 2.4.3, the TOC field now has a seperate setting for checkbox label. To support backcompat, if not set during upgrade, the 
	// checkbox label is set to the label of the TOC field if set else empty string.
	if ( isset( $field['template'] ) && $field['template'] === 'toc' && ! isset( $field['checkbox_label'] ) ){
		if ( isset( $field['label'] ) ) {
			$field['checkbox_label'] = $field['label'];
		} else {
			$field['checkbox_label'] = '';
		}
	}

	return $field;
}
