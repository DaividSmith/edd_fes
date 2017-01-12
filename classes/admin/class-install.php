<?php
/**
 * FES Installation and Automatic Upgrades
 *
 * This file handles setting up new
 * FES installs as well as performing
 * behind the scene upgrades between
 * FES versions.
 *
 * @package FES
 * @subpackage Install/Upgrade
 * @since 2.0.0
 *
 * @todo  Perhaps move the version upgrade functions
 *        into their own file. This file is getting 
 *        huge.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { 
	exit;
}

/**
 * FES Install.
 *
 * This class handles a new FES install
 * as well as automatic (non-user initiated) 
 * upgrade routines.
 *
 * @since 2.3.0
 * @access public
 */
class FES_Install {

	/**
	 * FES Settings.
	 *
	 * @since 2.0.0
	 * @access public
	 * @var array $new_settings When the init() function starts, initially
	 *      					contains the original settings. At the end 
	 *      				 	of init() contains the settings to save.
	 */	
	public $new_settings = array();

	/**
	 * Install/Upgrade routine.
	 *
	 * This function is what is called to actually 
	 * install FES data on new installs and to do
	 * behind the scenes upgrades on FES upgrades.
	 * If this function contains a bug, the results 
	 * can be catastrophic. This function gets the 
	 * highest priority in all of FES for unit tests.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @todo  I'd like to add preflight checks here.
	 * @todo  I'd like to add a recovery system here.
	 * @todo  Drop pre-FES 2.0 support.
	 * 
	 * @return void
	 */
	public function init() {
		// Attempt to get the current version.
		$version = get_option( 'fes_current_version', '2.0' );

		// If we don't need to upgrade, abort.
		if ( version_compare( $version, '2.4.3', '>=' )  ) {
			return;
		}

		// Get a copy of the current FES settings.
		$this->new_settings = get_option( 'edd_settings' );

		/**
		 * If you're having deja vu, you're not seeing things.
		 * This call was done 10 lines up. The first time we have
		 * to default the get_option call to 2.0, so in case of 
		 * a pre-2.0 install or a new install we can use 
		 * version_compare accurately. Now that we're passed the
		 * version check we now need to be able to differentiate
		 * pre-2.0 installs from new installs. Cancel your eye
		 * doctor appointment. Your eyes are fine :-).
		 */
		$version = get_option( 'fes_current_version', false );

		// if new install
		if ( !$version ) {
			$this->fes_new_install();
			// This is the version used for FES upgrade routines.
			update_option( 'fes_db_version', '2.3' );
		} else {
			// In FES 2.4 we switched from Redux to EDD settings.
			// Thus on pre-2.3 installs we need to get the old
			// settings array.
			if ( version_compare( $version, '2.4', '<' ) ) {
				$this->new_settings = get_option( 'fes_settings' );
			} else {
				// nothing. Already has new edd_settings
			}
			if ( version_compare( $version, '2.1', '<' ) ) {
				$this->fes_v21_upgrades();
			}

			if ( version_compare( $version, '2.2', '<' ) ) {
				$this->fes_v22_upgrades();
			}

			if ( version_compare( $version, '2.3', '<' ) ) {
				$this->fes_v23_upgrades();
			}

			if ( version_compare( $version, '2.3.2', '<' ) ) {
				$this->add_new_roles();
			}

			// In case we're upgrading from pre-2.4, let's save
			// the settings in the EDD settings array.
			if ( version_compare( $version, '2.4', '<' ) ) {
				$edd_options 		= get_option( 'edd_settings' );
				$this->new_settings = array_merge( $edd_options, $this->new_settings );
			}

			/** 
			 * The Great FES Eraser
			 *
			 * When you were a child you probably
			 * did all of your exams in pencil so you
			 * could correct mistakes later using an 
			 * eraser. This is sort of like a giant
			 * virtual eraser. We use schema correction
			 * to correct past mistakes (or "features")
			 * involving the saved schema (aka characteristics)
			 * of fields and forms. 
			 *
			 * Example:
			 * If a built in field saved without a `name` attribute
			 * we'd use schema correction to automatically fix this 
			 * mistake.
			 */
			$this->schema_corrector();
		}	

		// This is the version of the FES settings themselves
		update_option( 'fes_settings_version', '2.4.3' );

		// This is the version of FES installed
		update_option( 'fes_current_version', '2.4.3' );

		// This is where we save FES settings
		update_option( 'edd_settings', $this->new_settings );

		// This is where we redirect to the FES welcome page
		set_transient( '_fes_activation_redirect', true, 30 );

		// There's no code for this function below this. Just an explanation
		// of the FES core options.

		/** 
		 * Explanation of FES core options
		 *
		 * By now your head is probably spinning trying to figure
		 * out what all of these version options are for.
		 *
		 * Here's a basic rundown:
		 *
		 * fes_settings_version: Used to store the version 
		 * 						 of the FES settings. We use this
		 * 						 so we can do upgrade routines where
		 * 						 we'd have to do different actions based
		 * 						 on the version the settings were installed
		 * 						 in. For example: if we made a mistake with 
		 * 						 the value we saved as the default for 
		 * 						 a select setting, we can detect the version
		 * 						 containing this mistake and correct it.
		 *
		 * fes_current_version: This starts with the actual version FES was
		 * 						installed on. We use this version to 
		 * 						determine whether or not a site needs
		 * 						to run one of the behind the scenes
		 * 						FES upgrade routines. This version is updated
		 * 						every time a minor or major background upgrade
		 * 						routine is run. Generally lags behind the 
		 * 						FES_VERSION constant by at most a couple minor
		 * 						versions. Never lags behind by 1 major version
		 * 						or more.
		 *
		 * fes_db_version: 		This is different from fes_current_version.
		 * 						Unlike the former, this is used to determine
		 * 						if a site needs to run a *user* initiated
		 * 						upgrade routine (see FES_Upgrade class). This
		 * 						value is only update when a user initiated
		 * 						upgrade routine is done. Because we do very
		 * 						few user initiated upgrades compared to 
		 * 						automatic ones, this version can lag behind by
		 * 						2 or even 3 major versions. Generally contains
		 * 						the current major version.
		 *
		 * edd_settings:		This isn't actually our option. This is the 
		 * 						EDD core settings option. However since FES
		 * 						2.4 we save settings into it.
		 *
		 * fes_settings:		Deprecated, unused and no longer used since
		 * 						FES 2.4, this used to contain the FES settings
		 * 						when we used Redux. We automatically migrated 
		 * 						these to edd_settings, and the helper functions
		 * 						automatically pull the setting values from
		 * 						edd_settings once the settings have been 
		 * 						migrated. This option will be deleted as part
		 * 						of the FES 2.5 upgrade routine.
		 *
		 * edd_fes_options:		A long time ago in a galaxy far, far away....
		 * 						this used to contain FES settings when we used
		 * 						the Jigoshop settings panel in FES versions before
		 * 						2.1. This is long deprecated. The settings were
		 * 						transferred to fes_settings as part of the FES 2.2
		 * 						upgrade routine. The option itself is deleted as 
		 * 						part of the FES 2.3 routine because of the issues
		 * 						it caused due to it's size.
		 */			
	}


	/**
	 * New FES Install routine.
	 *
	 * This function installs all of the default
	 * things on new FES installs. Flight 4953 with 
	 * non-stop service to a whole world of 
	 * possibilities is now boarding.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @uses FES_Install::add_new_roles() Creates the default FES roles.
	 * @uses FES_Install::create_vendor_dashboard_page() Creates the vendor dashboard page.
	 * @uses FES_Install::create_vendor_page() Creates the vendor page.
	 * @uses FES_Install::create_submission_form() Creates the default submission form.
	 * @uses FES_Install::create_profile_form() Creates the default profile form.
	 * @uses FES_Install::create_registration_form() Creates the default registration form.
	 * @uses FES_Install::create_login_form() Creates the default login form.
	 * @uses FES_Install::create_vendor_contact_form() Creates the default vendor contact form.
	 * @uses FES_DB_Vendors::create_table() Creates the FES vendor table.
	 * 
	 * @return void
	 */
	public function fes_new_install() {
		$this->add_new_roles();
		$this->create_vendor_dashboard_page();
		$this->create_vendor_page();
		$this->create_submission_form();
		$this->create_profile_form();
		$this->create_registration_form();
		$this->create_login_form();
		$this->create_vendor_contact_form();
		$db = new FES_DB_Vendors();
		$db->create_table();

		// Set checkboxes that need to be default true
		$default_settings_to_true = array(
			'fes-use-css',
			'fes-allow-registrations',
			'fes-allow-applications',
			'fes-auto-approve-vendors',
			'fes-auto-approve-edits',
			'fes-allow-vendors-to-create-products',
			'fes-allow-vendors-to-create-products',
			'fes-allow-vendors-to-edit-products',
			'fes-allow-vendors-to-delete-products',
			'fes-allow-vendors-to-view-orders',
			'fes-admin-new-app-email-toggle',
			'fes-admin-new-submission-email-toggle',
			'fes-admin-new-submission-edit-email-toggle',
			'fes-vendor-new-app-email-toggle',
			'fes-vendor-app-approved-email-toggle',
			'fes-vendor-app-declined-email-toggle',
			'fes-vendor-new-auto-vendor-email-toggle',
			'fes-vendor-app-revoked-email-toggle',
			'fes-vendor-suspended-email-toggle',
			'fes-vendor-unsuspended-email-toggle',
			'fes-vendor-new-submission-email-toggle',
			'fes-vendor-submission-approved-email-toggle',
			'fes-vendor-submission-declined-email-toggle',
			'fes-vendor-submission-revoked-email-toggle',
		);
		foreach ( $default_settings_to_true as $setting ){
			edd_update_option( $setting, '1' );
		}
	}

	/**
	 * FES Version 2.1 upgrades.
	 *
	 * This function used to do the
	 * upgrade routine from FES 2.0->2.1.
	 *
	 * @since 2.1.0
	 * @access public
	 *
	 * @deprecated 2.2.0 No longer needed.
	 * 
	 * @return void
	 */
	public function fes_v21_upgrades() {
		// No longer needed. None of the upgrade routine is used anymore.
	}

	/**
	 * FES Version 2.2 upgrades.
	 *
	 * This function does the
	 * upgrade routine from FES 2.1->2.2.
	 *
	 * @since 2.2.0
	 * @access public
	 * 
	 * @return void
	 */
	public function fes_v22_upgrades() {
		// Convert settings panel from Jigoshop to Redux
		$old_settings = get_option( 'edd_fes_options', false );
		if ( $old_settings ) {
			// Submission form
			if ( isset( $old_settings['fes-submission-form'] ) && $old_settings['fes-submission-form'] != '' ) {
				$this->new_settings['fes-submission-form'] = $old_settings['fes-submission-form'];
			}

			// Profile form
			if ( isset( $old_settings['fes-profile-form'] ) && $old_settings['fes-profile-form'] != '' ) {
				$this->new_settings['fes-profile-form'] = $old_settings['fes-profile-form'];
			}

			// Application form
			if ( isset( $old_settings['fes-application-form'] ) && $old_settings['fes-application-form'] != '' ) {
				$this->new_settings['fes-application-form'] = $old_settings['fes-application-form'];
			}

			// Vendor form
			if ( isset( $old_settings['vendor-page'] ) && $old_settings['vendor-page'] != '' ) {
				$this->new_settings['fes-vendor-page'] = $old_settings['vendor-page'];
			}

			// Vendor Dashboard form
			if ( isset( $old_settings['vendor-dashboard-page'] ) && $old_settings['vendor-dashboard-page'] != '' ) {
				$this->new_settings['fes-vendor-dashboard-page'] = $old_settings['vendor-dashboard-page'];
			}

			// Vendor Dashboard notification
			if ( isset( $old_settings['dashboard-page-template'] ) && $old_settings['dashboard-page-template'] !== '' ) {
				$this->new_settings['fes-dashboard-notification'] = $old_settings['dashboard-page-template'];
			}

			// Allow Vendors Backend Access
			if ( isset( $old_settings['vendors_bea'] ) && $old_settings['vendors_bea'] !== '' ) {
				$this->new_settings['fes-allow-backend-access'] = $old_settings['vendors_bea'];
			}

			// Show Vendor Registration
			if ( isset( $old_settings['show_vendor_registration'] ) && $old_settings['show_vendor_registration'] !== '' ) {
				$this->new_settings['show_vendor_registration'] = $old_settings['show_vendor_registration'];
			}

			// Auto Approve Vendors
			if ( isset( $old_settings['edd_fes_auto_approve_vendors'] ) && $old_settings['edd_fes_auto_approve_vendors'] !== '' ) {
				$this->new_settings['fes-auto-approve-vendors'] = $old_settings['edd_fes_auto_approve_vendors'];
			}

			// Allow Vendors to Edit Products
			if ( isset( $old_settings['edd_fes_vendor_permissions_edit_product'] ) && $old_settings['edd_fes_vendor_permissions_edit_product'] !== '' ) {
				$this->new_settings['fes-allow-vendors-to-edit-products'] = $old_settings['edd_fes_vendor_permissions_edit_product'];
			}

			// Allow Vendors to Delete Products
			if ( isset( $old_settings['edd_fes_vendor_permissions_delete_product'] ) && $old_settings['edd_fes_vendor_permissions_delete_product'] !== '' ) {
				$this->new_settings['fes-allow-vendors-to-delete-products'] = $old_settings['edd_fes_vendor_permissions_delete_product'];
			}

			// Use EDD's CSS
			if ( isset( $old_settings['edd_fes_use_css'] ) && $old_settings['edd_fes_use_css'] !== '' ) {
				$this->new_settings['fes-use-css'] = $old_settings['edd_fes_use_css'];
			}

			// Admin notification on new vendor application
			if ( isset( $old_settings['edd_fes_notify_admin_new_app_toggle'] ) && $old_settings['edd_fes_notify_admin_new_app_toggle'] !== '' ) {
				$this->new_settings['fes-admin-new-app-email-toggle'] = $old_settings['edd_fes_notify_admin_new_app_toggle'];
			}

			// Admin message on new vendor application
			if ( isset( $old_settings['edd_fes_notify_admin_new_app_message'] ) && $old_settings['edd_fes_notify_admin_new_app_message'] != '' ) {
				$this->new_settings['fes-admin-new-app-email'] = $old_settings['edd_fes_notify_admin_new_app_message'];
			}

			// User message on new vendor application
			if ( isset( $old_settings['edd_fes_notify_user_new_app_message'] ) && $old_settings['edd_fes_notify_user_new_app_message'] != '' ) {
				$this->new_settings['fes-vendor-new-app-email'] = $old_settings['edd_fes_notify_user_new_app_message'];
			}

			// Admin message on new vendor submission
			if ( isset( $old_settings['new_edd_fes_submission_admin_message'] ) && $old_settings['new_edd_fes_submission_admin_message'] != '' ) {
				$this->new_settings['fes-admin-new-submission-email'] = $old_settings['new_edd_fes_submission_admin_message'];
			}

			// User message on vendor application accepted
			if ( isset( $old_settings['edd_fes_notify_user_app_accepted_message'] ) && $old_settings['edd_fes_notify_user_app_accepted_message'] != '' ) {
				$this->new_settings['fes-vendor-app-approved-email'] = $old_settings['edd_fes_notify_user_app_accepted_message'];
			}

			// User message on vendor application denied
			if ( isset( $old_settings['edd_fes_notify_user_app_denied_message'] ) && $old_settings['edd_fes_notify_user_app_denied_message'] != '' ) {
				$this->new_settings['fes-vendor-app-declined-email'] = $old_settings['edd_fes_notify_user_app_denied_message'];
			}

			// User message on new vendor submission
			if ( isset( $old_settings['new_edd_fes_submission_user_message'] ) && $old_settings['new_edd_fes_submission_user_message'] != '' ) {
				$this->new_settings['fes-vendor-new-submission-email'] = $old_settings['new_edd_fes_submission_user_message'];
			}

			// User message on new vendor submission accepted
			if ( isset( $old_settings['edd_fes_submission_accepted_message'] ) && $old_settings['edd_fes_submission_accepted_message'] != '' ) {
				$this->new_settings['fes-vendor-submission-approved-email'] = $old_settings['edd_fes_submission_accepted_message'];
			}

			// User message on new vendor submission declined
			if ( isset( $old_settings['edd_fes_submission_declined_message'] ) && $old_settings['edd_fes_submission_declined_message'] != '' ) {
				$this->new_settings['fes-vendor-submission-declined-email'] = $old_settings['edd_fes_submission_declined_message'];
			}

			// reCAPTCHA Public Key
			if ( isset( $old_settings['recaptcha_public'] ) && $old_settings['recaptcha_public'] != '' ) {
				$this->new_settings['fes-recaptcha-public-key'] = $old_settings['recaptcha_public'];
			}

			// reCAPTCHA Private Key
			if ( isset( $old_settings['recaptcha_private'] ) && $old_settings['recaptcha_private'] != '' ) {
				$this->new_settings['fes-recaptcha-private-key'] = $old_settings['recaptcha_private'];
			}
		}
		$this->create_registration_form();
		$this->create_login_form();
		$this->create_vendor_contact_form();

		// if application form 
		if ( isset( $this->new_settings['fes-application-form'] ) && $this->new_settings['fes-application-form'] != '' ) {
			// move fields to registration form
			$old_fields = get_post_meta( $this->new_settings['fes-application-form'], 'fes-form', true );
			$new_fields = get_post_meta( $this->new_settings['fes-registration-form'], 'fes-form', true );

			if ( is_array( $old_fields ) && is_array( $new_fields ) ) {
				$counter = 0;
				foreach ( $old_fields as $field ) {
					$key = 7 + $counter;
					if ( isset( $field['input_type'] ) && $field['input_type'] == 'image_upload' && isset( $field['is_meta'] ) && $field['is_meta'] == 'no' ) {
						$field['input_type'] = 'file_upload';
					}
					if ( isset( $field['template'] ) && $field['template'] == 'image_upload' && isset( $field['is_meta'] ) && $field['is_meta'] == 'no' ) {
						$field['template'] = 'file_upload';
					}
					// skip these fields as they are already in the new form
					$to_skip = array( 'last_name', 'first_name', 'user_email', 'username', 'password', 'display_name' );
					if ( isset( $field['template'] ) && !in_array( $field['template'] , $to_skip ) ) {
						$new_fields[$key] = $field;
						$counter++;
					}
				}
				update_post_meta( $this->new_settings['fes-registration-form'], 'fes-form', $new_fields );
			}
		}

		// if submission form
		if ( isset( $this->new_settings['fes-submission-form'] ) && $this->new_settings['fes-submission-form'] != '' ) {
			$old_fields = get_post_meta( $this->new_settings['fes-submission-form'], 'fes-form', true );
			if ( !is_array( $old_fields ) ) {
				return;
			}
			else {
				// replace image uploaders with file ones
				foreach ( $old_fields as $field ) {
					if ( isset( $field['input_type'] ) && $field['input_type'] == 'image_upload' ) {
						$field['input_type'] = 'file_upload';
					}
					if ( isset( $field['template'] ) && $field['template'] == 'image_upload' ) {
						$field['template'] = 'file_upload';
					}
				}
				update_post_meta( $this->new_settings['fes-submission-form'], 'fes-form', $old_fields );
			}
		}


		// if profile form
		if ( isset( $this->new_settings['fes-profile-form'] ) && $this->new_settings['fes-profile-form'] != '' ) {
			// add fields to profile form
			$old_fields = get_post_meta( $this->new_settings['fes-profile-form'], 'fes-form', true );
			$nextindex = 1;
			if ( !is_array( $old_fields ) ) {
				$old_fields = array();
			} else {
				// replace image uploaders with file ones
				foreach ( $old_fields as $field ) {
					if ( isset( $field['input_type'] ) && $field['input_type'] == 'image_upload' ) {
						$field['input_type'] = 'file_upload';
					}
					if ( isset( $field['template'] ) && $field['template'] == 'image_upload' ) {
						$field['template'] = 'file_upload';
					}
				}

				end( $old_fields );
				$last = key( $old_fields );
				$nextindex = $last + 1;
			}

			$old_fields[$nextindex] = array(
				'template' => 'text',
				'required' => 'yes',
				'label' => 'Name of Store',
				'name' => 'name_of_store',
				'is_meta' => 'yes',
				'help' => 'What would you like your store to be called?',
				'css' => '',
				'placeholder' => '',
				'default' => '',
				'size' => '40'
			);
			$nextindex++;
			$old_fields[$nextindex] = array(
				'template' => 'email',
				'required' => 'yes',
				'label' => 'Email to use for Contact Form',
				'name' => 'email_to_use_for_contact_form',
				'is_meta' => 'yes',
				'help' => 'This email, if filled in will be used for the vendor contact forms. if it is not filled in, the one from your user profile will be used.',
				'css' => '',
				'placeholder' => '',
				'default' => '',
				'size' => '40'
			);
			update_post_meta( $this->new_settings['fes-profile-form'], 'fes-form', $old_fields );
		}


		// foreach fes_form if has editor in the name, remove it ( only affects FES Forms from pre 2.2 )
		// Submission form
		if ( isset( $this->new_settings['fes-submission-form'] ) && $this->new_settings['fes-submission-form'] != '' ) {
			$id = $this->new_settings['fes-submission-form'];
			$update = array(
				'ID'           => $id,
				'post_title'   => __( 'Submission Form', 'edd_fes' ),
				'post_name'    =>'fes-submission-form'
			);
			wp_update_post( $update );
		}

		// Profile form
		if ( isset( $this->new_settings['fes-profile-form'] ) && $this->new_settings['fes-profile-form'] != '' ) {
			$id = $this->new_settings['fes-profile-form'];
			$update = array(
				'ID'           => $id,
				'post_title'   => __( 'Profile Form', 'edd_fes' ),
				'post_name'    =>'fes-profile-form'
			);
			wp_update_post( $update );
		}

	}

	/**
	 * FES Version 2.3 upgrades.
	 *
	 * This function does the
	 * upgrade routine from FES 2.2->2.3.
	 *
	 * @since 2.3.0
	 * @access public
	 * 
	 * @return void
	 */
	public function fes_v23_upgrades() {
		// Create Vendor Database table
		$db = new FES_DB_Vendors();
		$db->create_table();

		// Foreach form, ensure the field has a template and remove input_type. Also ensure password fields have names
		// submission form
		if ( isset( $this->new_settings['fes-submission-form'] ) && $this->new_settings['fes-submission-form'] != '' ) {
			fes_save_initial_submission_form( $this->new_settings['fes-submission-form'], false );
		} else {
			$this->create_submission_form();
		}

		// profile form
		if ( isset( $this->new_settings['fes-profile-form'] ) && $this->new_settings['fes-profile-form'] != '' ) {
			fes_save_initial_profile_form( $this->new_settings['fes-profile-form'], false );
		} else {
			$this->create_profile_form();
		}

		// registration form
		if ( isset( $this->new_settings['fes-registration-form'] ) && $this->new_settings['fes-registration-form'] != '' ) {
			fes_save_initial_registration_form( $this->new_settings['fes-registration-form'], false );
		} else {
			$this->create_registration_form();
		}

		// login form
		if ( isset( $this->new_settings['fes-login-form'] ) && $this->new_settings['fes-login-form'] != '' ) {
			fes_save_initial_login_form( $this->new_settings['fes-login-form'] );
		} else {
			$this->create_login_form();
		}

		//  vendor contact form
		if ( isset( $this->new_settings['fes-vendor-contact-form'] ) && $this->new_settings['fes-vendor-contact-form'] != '' ) {
			fes_save_initial_vendor_contact_form( $this->new_settings['fes-vendor-contact-form'] );
		} else {
			$this->create_vendor_contact_form();
		}

		// delete all fes-forms not in the 5 above
		$forms = get_posts( array( 'post_type' => 'fes-forms', 'fields' => 'ids', 'posts_per_page' => -1, '' ) );
		if ( $forms ) {
			foreach ( $forms as $form ) {
				if ( isset( $this->new_settings['fes-submission-form'] )          && $this->new_settings['fes-submission-form'] == $form  && $this->new_settings['fes-submission-form'] ) {
					continue;
				}
				else if ( isset( $this->new_settings['fes-profile-form'] )        && $this->new_settings['fes-profile-form'] == $form       && $this->new_settings['fes-profile-form'] ) {
						continue;
					}
				else if ( isset( $this->new_settings['fes-registration-form'] )   && $this->new_settings['fes-registration-form'] == $form   && $this->new_settings['fes-registration-form'] ) {
						continue;
					}
				else if ( isset( $this->new_settings['fes-login-form'] )     && $this->new_settings['fes-login-form'] == $form    && $this->new_settings['fes-login-form'] ) {
						continue;
					}
				else if ( isset( $this->new_settings['fes-vendor-contact-form'] ) && $this->new_settings['fes-vendor-contact-form'] == $form && $this->new_settings['fes-vendor-contact-form'] ) {
						continue;
					}
				else {
					wp_delete_post( $form, true );
				}
			}
		}

		$old_settings = get_option( 'fes_settings', array() );

		// Set the new vendor constant field
		if ( isset( $old_settings['fes-plugin-constants'] ) && isset( $old_settings['fes-plugin-constants'][4] ) && $old_settings['fes-plugin-constants'][4] != '' ) {
			$this->new_settings['fes-vendor-constant'] = $old_settings['fes-plugin-constants'][4];
		}

		// Set the new product constant field
		if ( isset( $old_settings['fes-plugin-constants'] ) && isset( $old_settings['fes-plugin-constants'][8] ) && $old_settings['fes-plugin-constants'][8] != '' ) {
			$this->new_settings['fes-product-constant'] = $old_settings['fes-plugin-constants'][8];
		}

		// delete old settings option from 2.1
		delete_option( 'edd_fes_options' );

		// delete all old applications
		$posts = get_posts( array(
				'nopaging'    => true,
				'orderby'     => 'title',
				'post_type'   => 'fes-applications',
				'post_status' => 'any',
				'order'    => 'ASC'
			) );
		if ( $posts ) {
			foreach ( $posts as $post ) {
				wp_delete_post( $post->ID, true );
			}
		}
	}

	/**
	 * FES Version 2.4 upgrades.
	 *
	 * This function does the
	 * upgrade routine from FES 2.3->2.4.
	 *
	 * @since 2.4.0
	 * @access public
	 * 
	 * @return void
	 */
	public function fes_v24_upgrades() {
		/**
		 * FES 2.4 Upgrade Routine Brief Summary:
		 * 1. Remove the user login field from the profile form
		 */
		// Remove user login field from the profile form
		if ( isset( $this->new_settings['fes-profile-form'] ) && $this->new_settings['fes-profile-form'] != '' ) {
			$old_fields = get_post_meta( $this->new_settings['fes-profile-form'], 'fes-form', true );
			$count = 0;
			if ( is_array( $old_fields ) ) {
				foreach ( $old_fields as $id => $field ) {
					if ( isset( $field['template'] ) && $field['template'] === 'user_login' ){
						continue;
					}
					
					$old_fields[ $count ] = $field; // save new field back
					$count++;
				}
				update_post_meta( $this->new_settings['fes-profile-form'], 'fes-form', $old_fields );
			}
		}
	}

	/**
	 * FES Schema correction.
	 * 
	 * When you were a child you probably
	 * did all of your exams in pencil so you
	 * could correct mistakes later using an 
	 * eraser. This is sort of like a giant
	 * virtual eraser. We use schema correction
	 * to correct past mistakes (or "features")
	 * involving the saved schema (aka characteristics)
	 * of fields and forms. If a built in field saved 
	 * without a `name` attribute we'd use schema correction
	 * to automatically fix this mistake.
	 *
	 * @since 2.3.0
	 * @access public
	 * 
	 * @return void
	 */
	public function schema_corrector(){
		// submission form
		if ( isset( $this->new_settings['fes-submission-form'] ) && $this->new_settings['fes-submission-form'] != '' ) {
			$old_fields = get_post_meta( $this->new_settings['fes-submission-form'], 'fes-form', true );
			if ( is_array( $old_fields ) ) {
				foreach ( $old_fields as $id => $field ) {
					$field = fes_upgrade_field( $field ); // upgrade field
					$old_fields[ $id ] = $field; // save new field back
				}
				update_post_meta( $this->new_settings['fes-submission-form'], 'fes-form', $old_fields );
			}
		}

		// profile form
		if ( isset( $this->new_settings['fes-profile-form'] ) && $this->new_settings['fes-profile-form'] != '' ) {
			$old_fields = get_post_meta( $this->new_settings['fes-profile-form'], 'fes-form', true );
			if ( is_array( $old_fields ) ) {
				foreach ( $old_fields as $id => $field ) {
					$field = fes_upgrade_field( $field ); // upgrade field
					$old_fields[ $id ] = $field; // save new field back
				}
				update_post_meta( $this->new_settings['fes-profile-form'], 'fes-form', $old_fields );
			}
		}

		// registration form
		if ( isset( $this->new_settings['fes-registration-form'] ) && $this->new_settings['fes-registration-form'] != '' ) {
			$old_fields = get_post_meta( $this->new_settings['fes-registration-form'], 'fes-form', true );
			if ( is_array( $old_fields ) ) {
				foreach ( $old_fields as $id => $field ) {
					$field = fes_upgrade_field( $field ); // upgrade field
					$old_fields[ $id ] = $field; // save new field back
				}
				update_post_meta( $this->new_settings['fes-registration-form'], 'fes-form', $old_fields );
			}
		}

		// login form
		if ( isset( $this->new_settings['fes-login-form'] ) && $this->new_settings['fes-login-form'] != '' ) {
			$old_fields = get_post_meta( $this->new_settings['fes-login-form'], 'fes-form', true );
			if ( is_array( $old_fields ) ) {
				foreach ( $old_fields as $id => $field ) {
					$field = fes_upgrade_field( $field ); // upgrade field
					$old_fields[ $id ] = $field; // save new field back
				}
				update_post_meta( $this->new_settings['fes-login-form'], 'fes-form', $old_fields );
			}
		}

		//  vendor contact form
		if ( isset( $this->new_settings['fes-vendor-contact-form'] ) && $this->new_settings['fes-vendor-contact-form'] != '' ) {
			$old_fields = get_post_meta( $this->new_settings['fes-vendor-contact-form'], 'fes-form', true );
			if ( is_array( $old_fields ) ) {
				foreach ( $old_fields as $id => $field ) {
					$field = fes_upgrade_field( $field ); // upgrade field
					$old_fields[ $id ] = $field; // save new field back
				}
				update_post_meta( $this->new_settings['fes-vendor-contact-form'], 'fes-form', $old_fields );
			}
		}
	}

	/**
	 * Create Vendor Dashboard Page.
	 * 
	 * Checks to ensure the vendor
	 * dashboard page doesn't already exist
	 * and if it doesn't then creates it, and
	 * inserts the post id into the FES settings.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @global  $wpdb WordPress database object for page retrieval.
	 * 
	 * @return void
	 */
	public function create_vendor_dashboard_page() {
		global $wpdb;
		$page_id = isset( $this->new_settings['fes-vendor-dashboard-page'] ) ? $this->new_settings['fes-vendor-dashboard-page'] : 0;
		if ( $page_id > 0 && get_post( $page_id ) ) {
			return;
		}
		$page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM " . $wpdb->posts . " WHERE post_name = %s LIMIT 1;", "vendor-dashboard" ) );
		if ( $page_found ) {
			if ( !$page_id ) {
				$this->new_settings['fes-vendor-dashboard-page'] = $page_found;
				return;
			}
			return;
		}
		$page_data = array(
			'post_status' => 'publish',
			'post_type' => 'page',
			'post_author' => get_current_user_id(),
			'post_name' => 'vendor-dashboard',
			'post_title' => __( 'Vendor Dashboard', 'edd_fes' ),
			'post_content' => '[fes_vendor_dashboard]',
			'comment_status' => 'closed'
		);
		$page_id   = wp_insert_post( $page_data );
		$this->new_settings['fes-vendor-dashboard-page'] = $page_id;
	}

	/**
	 * Create Vendor Page.
	 * 
	 * Checks to ensure the vendor
	 * page doesn't already exist
	 * and if it doesn't then creates it, and
	 * inserts the post id into the FES settings.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @global  $wpdb WordPress database object for page retrieval.
	 * 
	 * @return void
	 */
	public function create_vendor_page() {
		global $wpdb;
		$page_id = isset( $this->new_settings['fes-vendor-page'] ) ? $this->new_settings['fes-vendor-page'] : 0;
		if ( $page_id > 0 && get_post( $page_id ) ) {
			return;
		}
		$page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM " . $wpdb->posts . " WHERE post_name = %s LIMIT 1;", "vendor" ) );
		if ( $page_found ) {
			if ( !$page_id ) {
				$this->new_settings['fes-vendor-page'] = $page_found;
				return;
			}
			return;
		}
		$page_data = array(
			'post_status'    => 'publish',
			'post_type'      => 'page',
			'post_author'    => get_current_user_id(),
			'post_name'   => 'vendor',
			'post_title'   => __( 'Vendor', 'edd_fes' ),
			'post_content'   => '[downloads]',
			'comment_status' => 'closed'
		);
		$page_id   = wp_insert_post( $page_data );
		$this->new_settings['fes-vendor-page'] = $page_id;
	}

	/**
	 * Create Submission Form.
	 * 
	 * Checks to ensure the submission
	 * form doesn't already exist
	 * and if it doesn't then creates it, and
	 * inserts the post id into the FES settings.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @global  $wpdb WordPress database object for post retrieval.
	 * 
	 * @return void
	 */
	public function create_submission_form( ) {
		global $wpdb;
		$slug = "fes-submission-form";
		$page_id = isset( $this->new_settings[$slug] ) ? $this->new_settings[$slug] : 0;
		if ( $page_id > 0 && get_post( $page_id ) ) {
			return;
		}
		$page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM " . $wpdb->posts . " WHERE post_name = %s LIMIT 1;", $slug ) );
		if ( $page_found ) {
			if ( !$page_id ) {
				$this->new_settings['fes-submission-form'] = $page_found;
				return;
			}
			return;
		}

		$page_data = array(
			'post_status' => 'publish',
			'post_type' => 'fes-forms',
			'post_author' => get_current_user_id(),
			'post_title' => __( 'Submission Form', 'edd_fes' )
		);
		$page_id   = wp_insert_post( $page_data );
		fes_save_initial_submission_form( $page_id );
		$this->new_settings['fes-submission-form'] = $page_id;
	}

	/**
	 * Create Profile Form.
	 * 
	 * Checks to ensure the profile
	 * form doesn't already exist
	 * and if it doesn't then creates it, and
	 * inserts the post id into the FES settings.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @global  $wpdb WordPress database object for post retrieval.
	 * 
	 * @return void
	 */
	public function create_profile_form() {
		global $wpdb;
		$slug = "fes-profile-form";
		$page_id = isset( $this->new_settings[$slug] ) ? $this->new_settings[$slug] : 0;
		if ( $page_id > 0 && get_post( $page_id ) ) {
			return;
		}
		$page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM " . $wpdb->posts . " WHERE post_name = %s LIMIT 1;", $slug ) );
		if ( $page_found ) {
			if ( !$page_id ) {
				$this->new_settings[$slug] = $page_found;
				return;
			}
			return;
		}

		$page_data = array(
			'post_status' => 'publish',
			'post_type' => 'fes-forms',
			'post_author' => get_current_user_id(),
			'post_title' => __( 'Profile Form', 'edd_fes' )
		);
		$page_id   = wp_insert_post( $page_data );
		fes_save_initial_profile_form( $page_id );
		$this->new_settings['fes-profile-form'] = $page_id;
	}

	/**
	 * Create Registration Form.
	 * 
	 * Checks to ensure the registration
	 * form doesn't already exist
	 * and if it doesn't then creates it, and
	 * inserts the post id into the FES settings.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @global  $wpdb WordPress database object for post retrieval.
	 * 
	 * @return void
	 */
	public function create_registration_form() {
		global $wpdb;
		$slug = "fes-registration-form";
		$page_id = isset( $this->new_settings[$slug] ) ? $this->new_settings[$slug] : 0;
		if ( $page_id > 0 && get_post( $page_id ) ) {
			return;
		}
		$page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM " . $wpdb->posts . " WHERE post_name = %s LIMIT 1;", $slug ) );
		if ( $page_found ) {
			if ( !$page_id ) {
				$this->new_settings[$slug] = $page_found;
				return;
			}
			return;
		}

		$page_data = array(
			'post_status' => 'publish',
			'post_type' => 'fes-forms',
			'post_author' => get_current_user_id(),
			'post_title' => __( 'Registration Form', 'edd_fes' )
		);
		$page_id   = wp_insert_post( $page_data );
		fes_save_initial_registration_form( $page_id );
		$this->new_settings['fes-registration-form'] = $page_id;
	}

	/**
	 * Create Login Form.
	 * 
	 * Checks to ensure the login
	 * form doesn't already exist
	 * and if it doesn't then creates it, and
	 * inserts the post id into the FES settings.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @global  $wpdb WordPress database object for post retrieval.
	 * 
	 * @return void
	 */
	public function create_login_form() {
		global $wpdb;
		$slug = "fes-login-form";
		$page_id = isset( $this->new_settings[$slug] ) ? $this->new_settings[$slug] : 0;
		if ( $page_id > 0 && get_post( $page_id ) ) {
			return;
		}
		$page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM " . $wpdb->posts . " WHERE post_name = %s LIMIT 1;", $slug ) );
		if ( $page_found ) {
			if ( !$page_id ) {
				$this->new_settings['fes-login-form'] = $page_found;
				return;
			}
			return;
		}

		$page_data = array(
			'post_status' => 'publish',
			'post_type' => 'fes-forms',
			'post_author' => get_current_user_id(),
			'post_title' => __( 'Login Form', 'edd_fes' )
		);
		$page_id   = wp_insert_post( $page_data );
		fes_save_initial_login_form( $page_id );
		$this->new_settings['fes-login-form'] = $page_id;
	}

	/**
	 * Create Vendor Contact Form.
	 * 
	 * Checks to ensure the vendor contact
	 * form doesn't already exist
	 * and if it doesn't then creates it, and
	 * inserts the post id into the FES settings.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @global  $wpdb WordPress database object for post retrieval.
	 * 
	 * @return void
	 */
	public function create_vendor_contact_form() {
		global $wpdb;
		$slug = "fes-vendor-contact-form";
		$page_id = isset( $this->new_settings[$slug] ) ? $this->new_settings[$slug] : 0;
		if ( $page_id > 0 && get_post( $page_id ) ) {
			return;
		}
		$page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM " . $wpdb->posts . " WHERE post_name = %s LIMIT 1;", $slug ) );
		if ( $page_found ) {
			if ( !$page_id ) {
				$this->new_settings['fes-vendor-contact-form'] = $page_found;
				return;
			}
			return;
		}

		$page_data = array(
			'post_status' => 'publish',
			'post_type' => 'fes-forms',
			'post_author' => get_current_user_id(),
			'post_title' => __( 'Vendor Contact Form', 'edd_fes' )
		);
		$page_id   = wp_insert_post( $page_data );
		fes_save_initial_vendor_contact_form( $page_id );
		$this->new_settings['fes-vendor-contact-form'] = $page_id;
	}

	/**
	 * Create FES roles.
	 * 
	 * Adds the FES roles.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @global $wp_roles WordPress roles. 
	 * 
	 * @return void
	 */
	public function add_new_roles() {
		global $wp_roles;
		remove_role( 'frontend_vendor' );
		add_role( 'frontend_vendor', 'Frontend Vendor', array(
			'read' => true,
			'edit_posts' => true,
			'upload_files' => true,
		) );
		if ( class_exists( 'WP_Roles' ) && !isset( $wp_roles ) )
			$wp_roles = new WP_Roles();
		if ( is_object( $wp_roles ) ) {
			$capabilities     = array();
			$capability_types = array(
				'product'
			);
			foreach ( $capability_types as $capability_type ) {
				$capabilities[ $capability_type ] = array(
					// Post type
					"edit_{$capability_type}",
					"read_{$capability_type}",
					"delete_{$capability_type}",
					"edit_{$capability_type}s",
					"read_private_{$capability_type}s",
					"edit_private_{$capability_type}s",
					// Terms
					"manage_{$capability_type}_terms",
					"edit_{$capability_type}_terms",
					"assign_{$capability_type}_terms"
				);
			}
			foreach ( $capabilities as $cap_group ) {
				foreach ( $cap_group as $cap ) {
					$wp_roles->add_cap( 'frontend_vendor', $cap );
				}
			}
			$wp_roles->add_cap( 'frontend_vendor', 'edit_product' );
			$wp_roles->add_cap( 'frontend_vendor', 'edit_products' );
			$wp_roles->add_cap( 'frontend_vendor', 'upload_files' );
			$wp_roles->add_cap( 'frontend_vendor', 'assign_product_terms' );
			$wp_roles->add_cap( 'frontend_vendor', 'delete_product' );
			$wp_roles->add_cap( 'frontend_vendor', 'delete_products' );

			$wp_roles->add_cap( 'administrator', 'fes_is_admin' );
			$wp_roles->add_cap( 'editor', 'fes_is_admin' );
		}
	}
}
