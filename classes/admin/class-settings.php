<?php
/**
 * FES Settings
 *
 * This file handles registering
 * all of FES's settings.
 *
 * @package FES
 * @subpackage Settings
 * @since 2.4.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FES Settings.
 *
 * Registers FES settings into EDD.
 *
 * @since 2.4.0
 * @access public
 */
class FES_Settings {

	/**
	 * FES Settings Actions.
	 *
	 * Runs actions required to add
	 * settings, setting tabs, and setting
	 * sections.
	 *
	 * @since 2.4.0
	 * @access public
	 *
	 * @return void
	 */
	function __construct() {
		add_filter( 'edd_registered_settings', array( $this, 'edd_settings_panel' ), 1, 1 );
		add_filter( 'edd_settings_tabs', array( $this, 'edd_settings_panel_add_tab' ), 1 );
		add_filter( 'edd_settings_sections', array( $this, 'edd_settings_panel_add_sections' ), 1, 1 );
	}

	/**
	 * FES Settings Panel.
	 *
	 * Registers all of FES's settings
	 * into the EDD settings system.
	 *
	 * @since 2.4.0
	 * @access public
	 *
	 * @param  array $settings EDD settings array.
	 * @return array EDD settings array.
	 */
	public function edd_settings_panel( $settings ) {
		$sections = array();
		$sections['main'] = array(
			'fes-vendor-constant' => array(
				'id' => 'fes-vendor-constant',
				'type' => 'text',
				'name' => __( 'Rename "vendor"', 'edd_fes' ),
				'desc' => __( 'This will change all mentions of "vendor" within FES', 'edd_fes' ),
				'std' =>  'vendor',
			),
			'fes-product-constant' => array(
				'id' => 'fes-product-constant',
				'type' => 'text',
				'name' => __( 'Rename "product"', 'edd_fes' ),
				'desc' => __( 'This will change all mentions of "product" within FES', 'edd_fes' ),
				'std' =>  'product',
			),
			'fes-dashboard-notification' => array(
				'id'=>'fes-dashboard-notification',
				'type' => 'rich_editor',
				'name' => __( 'Vendor Announcement', 'edd_fes' ),
				'desc' => __( 'Use this to announce things to your vendors. Appears on the Vendor Dashboard Page once logged in.', 'edd_fes' ),
				'std' =>  __( 'This is the vendor dashboard. Add welcome text or any other information that is applicable to your vendors.', 'edd_fes' )
			),
			'fes-use-css' => array(
				'id'=> 'fes-use-css',
				'type' => 'checkbox',
				'name' => __( 'Use FES\'s CSS', 'edd_fes' ),
				'desc' => __( 'Uncheck this to turn off FES\'s CSS. We only recommend that if your theme or custom CSS is providing styles for FES.', 'edd_fes' )
			),
		);

		$sections['forms'] = array(
			'fes-vendor-dashboard-page' => array(
				'id' => 'fes-vendor-dashboard-page',
				'type'        => 'select',
				'options'     => edd_get_pages(),
				'chosen'      => true,
				'placeholder' => __( 'Select a page', 'edd_fes' ),
				'name' => __( 'Vendor Dashboard Page', 'edd_fes' ),
				'desc'=> __( 'This setting is used by FES to determine which page is the vendor dashboard. Only change if you are using a custom dashboard page. The page must contain the [fes_vendor_dashboard] shortcode.', 'edd_fes' ),
			),
			'fes-vendor-page' => array(
				'id' => 'fes-vendor-page',
				'type'        => 'select',
				'options'     => edd_get_pages(),
				'chosen'      => true,
				'placeholder' => __( 'Select a page', 'edd_fes' ),
				'name' => __( 'Vendor Page', 'edd_fes' ),
				'desc'=> __( 'This setting is used by FES to determine which page is the vendor store page. Only change if you are using a custom vendor store page. The page must contain the [downloads] shortcode.', 'edd_fes' ),
			),
			'fes-allow-multiple-purchase-mode' => array(
				'id'=> 'fes-allow-multiple-purchase-mode',
				'type' => 'checkbox',
				'name' => __( 'Enable Multiple Purchase Mode for all vendor products', 'edd_fes' ),
				'desc' => __( 'Check this box to allow customers to purchase multiple variations of a vendor product simultaneously.', 'edd_fes' ),
			),
			'fes-show-custom-meta' => array(
				'id'=> 'fes-show-custom-meta',
				'type' => 'checkbox',
				'name' => __( 'Show custom fields on the download?', 'edd_fes' ),
				'desc' => __( 'Checking this box allows you to select which fields to show on the product page using the radio buttons on the top of each field on the submission formbuilder.', 'edd_fes' ),
			),
			'fes-submission-form' => array(
				'id' => 'fes-submission-form',
				'type'        => 'select',
				'options'     => fes_get_forms(),
				'chosen'      => true,
				'placeholder' => __( 'Select a form', 'edd_fes' ),
				'name' => __( 'Submission Form', 'edd_fes' ),
			),
			'fes-profile-form' => array(
				'id' => 'fes-profile-form',
				'type'        => 'select',
				'options'     => fes_get_forms(),
				'chosen'      => true,
				'placeholder' => __( 'Select a form', 'edd_fes' ),
				'name' => __( 'Profile Form', 'edd_fes' ),
			),
			'fes-login-form' => array(
				'id' => 'fes-login-form',
				'type'        => 'select',
				'options'     => fes_get_forms(),
				'chosen'      => true,
				'placeholder' => __( 'Select a form', 'edd_fes' ),
				'name' => __( 'Login Form', 'edd_fes' ),
			),
			'fes-registration-form' => array(
				'id' => 'fes-registration-form',
				'type'        => 'select',
				'options'     => fes_get_forms(),
				'chosen'      => true,
				'placeholder' => __( 'Select a form', 'edd_fes' ),
				'name' => __( 'Registration Form', 'edd_fes' ),
			),
			'fes-vendor-contact-form' => array(
				'id' => 'fes-vendor-contact-form',
				'type'        => 'select',
				'options'     => fes_get_forms(),
				'chosen'      => true,
				'placeholder' => __( 'Select a form', 'edd_fes' ),
				'name' => __( 'Vendor Contact Form', 'edd_fes' )
			),
		);

		$sections['permissions'] = array(
			'fes-allow-registrations' => array(
				'id'=> 'fes-allow-registrations',
				'type' => 'checkbox',
				'name' => __( 'Registration', 'edd_fes' ),
				'desc'=> __( 'Allow guests to apply to become a vendor', 'edd_fes' ),
			),
			'fes-allow-applications' => array(
				'id'=> 'fes-allow-applications',
				'type' => 'checkbox',
				'name' => __( 'Applications', 'edd_fes' ),
				'desc'=> __( 'Allow existing WordPress users to apply to become a vendor', 'edd_fes' ),
			),
			'fes-allow-backend-access' => array(
				'id'=> 'fes-allow-backend-access',
				'type' => 'checkbox',
				'name' => __( 'Allow Access to WP Backend?', 'edd_fes' ),
				'desc'=> __( 'Check this box to allow vendors to access the WordPress admin. Does not remove the admin bar.', 'edd_fes' ),
			),
			'fes-remove-admin-bar' => array(
				'id'=> 'fes-remove-admin-bar',
				'type' => 'checkbox',
				'name' => __( 'Show the Admin Bar?', 'edd_fes' ),
				'desc'=> __( 'Check this box to allow vendors to see the WordPress admin bar. Applies to vendor users that are not admins.', 'edd_fes' ),
			),
			'fes-auto-approve-vendors' => array(
				'id'=> 'fes-auto-approve-vendors',
				'type' => 'checkbox',
				'name' => __( 'Automatically Approve Vendors?', 'edd_fes' ),
				'desc'=> __( 'Check this box to automatically approve vendor applications. If not checked, vendors will not be able to submit products until their account is approved by a site admin.', 'edd_fes' ),
			),
			'fes-auto-approve-submissions' => array(
				'id'=> 'fes-auto-approve-submissions',
				'type' => 'checkbox',
				'name' => __( 'Automatically Approve Submissions?', 'edd_fes' ),
				'desc'=> __( 'Check this box to automatically approve vendor product submissions. If not checked, vendor products will be saved as pending until their account is approved by a site admin.', 'edd_fes' ),
			),
			'fes-auto-approve-edits' => array(
				'id'=> 'fes-auto-approve-edits',
				'type' => 'checkbox',
				'name' => __( 'Automatically Approve Vendor Edits?', 'edd_fes' ),
				'desc'=> __( 'Check this box to automatically approve edits vendors make to products. If not checked, vendor products will be changed to pending status (removed from store) until manually approved.', 'edd_fes' ),
			),
			'fes-allow-vendors-to-create-products' => array(
				'id'=> 'fes-allow-vendors-to-create-products',
				'type' => 'checkbox',
				'name' => __( 'Allow Vendors to Create Products?', 'edd_fes' ),
				'desc'=> __( 'Check this box to allow vendors to create products.', 'edd_fes' ),
			),
			'fes-allow-vendors-to-edit-products' => array(
				'id'=> 'fes-allow-vendors-to-edit-products',
				'type' => 'checkbox',
				'name' => __( 'Allow Vendors to Edit Products?', 'edd_fes' ),
				'desc'=> __( 'Check this box to allow vendors to edit their existing products. If vendors cannot edit products, after submitting a new product they will be redirected to the new product form instead of being redirected to edit the product they just submitted.', 'edd_fes' ),
			),
			'fes-allow-vendors-to-delete-products' => array(
				'id'=> 'fes-allow-vendors-to-delete-products',
				'type' => 'checkbox',
				'name' => __( 'Allow Vendors to Delete Products?', 'edd_fes' ),
				'desc'=> __( 'Check this box to allow vendors to delete their products.', 'edd_fes' ),
			),
			'fes-allow-vendors-to-view-orders' => array(
				'id'=> 'fes-allow-vendors-to-view-orders',
				'type' => 'checkbox',
				'name' => __( 'Allow Vendors to View Orders?', 'edd_fes' ),
				'desc'=> __( 'Check this box to allow vendors to view customer orders containing their products.', 'edd_fes' ),
			),
		);
		$sections['emails'] = array(
			'fes-admin-vendor-email-divider' => array(
				'id'   =>'fes-admin-vendor-email-divider',
				'name' => __( 'Emails to Admin', 'edd_fes' ),
				'type' => 'header',
			),
			'fes-admin-new-app-email-toggle' => array(
				'id'=> 'fes-admin-new-app-email-toggle',
				'type' => 'checkbox',
				'name' => __( 'Vendor Application', 'edd_fes' ),
				'desc' => __( 'Check this box to email site admins when a person applies to become a vendor. It is only sent if the "Automatically Approve Vendors" setting is off.' , 'edd_fes' ),
			),
			'fes-admin-new-app-email' => array(
				'id' => 'fes-admin-new-app-email',
				'type' => 'rich_editor',
				'name' => __( 'Vendor Application Email Contents', 'edd_fes' ),
				'desc' => __( 'The email body for the vendor application email. A list of email tags you can use is ', 'edd_fes' ).'<a href="http://docs.easydigitaldownloads.com/article/338-frontend-submissions-email-configuration" target="_blank" >'.__( ' in our documentation', 'edd_fes' ).'</a>.',
				'std' =>  '{fullname} has applied to become a vendor.',
			),
			'fes-admin-new-submission-email-toggle' => array(
				'id'=> 'fes-admin-new-submission-email-toggle',
				'type' => 'checkbox',
				'name' => __( 'New Product', 'edd_fes' ),
				'desc' => __( 'Check this box to email site admins when a vendor submits a new product. It is only sent if the "Automatically Approve Submissions" setting is off.', 'edd_fes' ),
			),
			'fes-admin-new-submission-email' => array(
				'id' => 'fes-admin-new-submission-email',
				'type' => 'rich_editor',
				'name' => __( 'New Product Email Contents', 'edd_fes' ),
				'desc' => __( 'The email body for the new product submissions. A list of email tags you can use is ', 'edd_fes' ).'<a href="http://docs.easydigitaldownloads.com/article/338-frontend-submissions-email-configuration" target="_blank" >'.__( ' in our documentation', 'edd_fes' ).'</a>.',
				'std' =>  'Vendor {fullname} has submitted the following submission for review:
Name of Submission: {post-title}
Content of Submission: {post-content}
Submission Category: {post-categories} n
Submission Tags: {post-tags}
Date of Submission: {post-date}',
			),
			'fes-admin-new-submission-edit-email-toggle' => array(
				'id'=> 'fes-admin-new-submission-edit-email-toggle',
				'type' => 'checkbox',
				'name' => __( 'Edited Product', 'edd_fes' ),
				'desc' => __( 'Check this box to email site admins when a vendor edits an existing product. It is only sent if the "Automatically Approve Vendor Edits" setting is off.', 'edd_fes' ),
			),
			'fes-admin-new-submission-edit-email' => array(
				'id' => 'fes-admin-new-submission-edit-email',
				'type' => 'rich_editor',
				'name' => __( 'Edited Product Email Contents', 'edd_fes' ),
				'desc' => __( 'The email body for edited product submissions. A list of email tags you can use is ', 'edd_fes' ).'<a href="http://docs.easydigitaldownloads.com/article/338-frontend-submissions-email-configuration" target="_blank" >'.__( ' in our documentation', 'edd_fes' ).'</a>.',
				'std' =>  'Vendor {fullname} has submitted the following edit for review:
Name of Submission: {post-title}
Content of Submission: {post-content}
Submission Category: {post-categories} n
Submission Tags: {post-tags}',
			),
			'fes-vendor-registration-email-divider' => array(
				'id'   =>'fes-vendor-registration-email-divider',
				'name' => __( 'Emails to Vendors', 'edd_fes' ),
				'type' => 'header',
			),
			'fes-vendor-new-app-email-toggle' => array(
				'id'=> 'fes-vendor-new-app-email-toggle',
				'type' => 'checkbox',
				'name' => __( 'Application Received', 'edd_fes' ),
				'desc' => __( 'Check this box to email site admins when a person applies to become a vendor. It is only sent if the "Automatically Approve Vendors" setting is off.', 'edd_fes' ),
			),
			'fes-vendor-new-app-email' => array(
				'id' => 'fes-vendor-new-app-email',
				'type' => 'rich_editor',
				'name' => __( 'Application Received Email Contents', 'edd_fes' ),
				'desc' => __( 'The email body for vendor application submissions. A list of email tags you can use is ', 'edd_fes' ).'<a href="http://docs.easydigitaldownloads.com/article/338-frontend-submissions-email-configuration" target="_blank" >'.__( ' in our documentation', 'edd_fes' ).'</a>.',
				'std' =>  'Dear {firstname},
	Your application to become a vendor has been received.

	We will process it as soon as possible.',
			),
			'fes-vendor-app-approved-email-toggle' => array(
				'id'=> 'fes-vendor-app-approved-email-toggle',
				'type' => 'checkbox',
				'name' => __( 'Application Approved', 'edd_fes' ),
				'desc' => __( 'Check this box to email a vendor when his or her application is approved. It is only sent if the "Automatically Approve Vendors" setting is off.', 'edd_fes' ),
			),
			'fes-vendor-app-approved-email' => array(
				'id' => 'fes-vendor-app-approved-email',
				'type' => 'rich_editor',
				'name' => __( 'Application Approved Email Contents', 'edd_fes' ),
				'desc' => __( 'The email body for the application approved email. A list of email tags you can use is ', 'edd_fes' ).'<a href="http://docs.easydigitaldownloads.com/article/338-frontend-submissions-email-configuration" target="_blank" >'.__( ' in our documentation', 'edd_fes' ).'</a>.',
				'std' =>  'Dear {firstname},
	Your application to become a vendor has been approved.',
			),
			'fes-vendor-app-declined-email-toggle' => array(
				'id'=> 'fes-vendor-app-declined-email-toggle',
				'type' => 'checkbox',
				'name' => __( 'Application Declined', 'edd_fes' ),
				'desc' => __( 'Check this box to email a vendor when his or her application is declined. It is only sent if the "Automatically Approve Vendors" setting is off.', 'edd_fes' ),
			),
			'fes-vendor-app-declined-email' => array(
				'id' => 'fes-vendor-app-declined-email',
				'type' => 'rich_editor',
				'name' => __( 'Application Declined Email Contents', 'edd_fes' ),
				'desc' => __( 'The email body for the application declined email. A list of email tags you can use is ', 'edd_fes' ).'<a href="http://docs.easydigitaldownloads.com/article/338-frontend-submissions-email-configuration" target="_blank" >'.__( ' in our documentation', 'edd_fes' ).'</a>.',
				'std' =>  'Dear {firstname},
	Your application to become a vendor has been declined!',
			),
			'fes-vendor-new-auto-vendor-email-toggle' => array(
				'id'=> 'fes-vendor-new-auto-vendor-email-toggle',
				'type' => 'checkbox',
				'name' => __( 'Application Auto-Approved', 'edd_fes' ),
				'desc' => __( 'Check this box to email a vendor when his or her application is automatically approved when the "Automatically Approve Vendors" setting is on.', 'edd_fes' ),
				'name' => __( 'Vendor Auto-Approved', 'edd_fes' ),
			),
			'fes-vendor-new-auto-vendor-email' => array(
				'id' => 'fes-vendor-new-auto-vendor-email',
				'type' => 'rich_editor',
				'name' => __( 'Application Auto-Approved Email Contents', 'edd_fes' ),
				'desc' => __( 'The email body for the application auto-approved email. A list of email tags you can use is ', 'edd_fes' ).'<a href="http://docs.easydigitaldownloads.com/article/338-frontend-submissions-email-configuration" target="_blank" >'.__( ' in our documentation', 'edd_fes' ).'</a>.',
				'std' =>  'Dear {firstname},
	Your application to become a vendor has been approved.',
			),
			'fes-vendor-app-revoked-email-toggle' => array(
				'id'=> 'fes-vendor-app-revoked-email-toggle',
				'type' => 'checkbox',
				'name' => __( 'Vendor Revoked', 'edd_fes' ),
				'desc' => __( 'Check this box to email a vendor when his or her vendor status is revoked.', 'edd_fes' ),
			),
			'fes-vendor-app-revoked-email' => array(
				'id' => 'fes-vendor-app-revoked-email',
				'type' => 'rich_editor',
				'name' => __( 'Vendor Revoked Email Contents', 'edd_fes' ),
				'desc' => __( 'The email body for the vendor revoked email. A list of email tags you can use is ', 'edd_fes' ).'<a href="http://docs.easydigitaldownloads.com/article/338-frontend-submissions-email-configuration" target="_blank" >'.__( ' in our documentation', 'edd_fes' ).'</a>.',
				'std' =>  'Dear {firstname},
	Your vendor account has been revoked.',
			),
			'fes-vendor-suspended-email-toggle' => array(
				'id'=> 'fes-vendor-suspended-email-toggle',
				'type' => 'checkbox',
				'name' => __( 'Vendor Suspended', 'edd_fes' ),
				'desc' => __( 'Check this box to email a vendor when his or her vendor status is suspended.', 'edd_fes' ),
			),
			'fes-vendor-suspended-email' => array(
				'id' => 'fes-vendor-suspended-email',
				'type' => 'rich_editor',
				'name' => __( 'Vendor Suspended Email Contents', 'edd_fes' ),
				'desc' => __( 'The email body for the vendor suspended email. A list of email tags you can use is ', 'edd_fes' ).'<a href="http://docs.easydigitaldownloads.com/article/338-frontend-submissions-email-configuration" target="_blank" >'.__( ' in our documentation', 'edd_fes' ).'</a>.',
				'std' =>  'Dear {firstname},
	Your vendor account has been suspended.',
			),
			'fes-vendor-unsuspended-email-toggle' => array(
				'id'=> 'fes-vendor-unsuspended-email-toggle',
				'type' => 'checkbox',
				'name' => __( 'Vendor Unsuspended', 'edd_fes' ),
				'desc' => __( 'Check this box to email a vendor when his or her vendor status is unsuspended.', 'edd_fes' ),
			),
			'fes-vendor-unsuspended-email' => array(
				'id' => 'fes-vendor-unsuspended-email',
				'type' => 'rich_editor',
				'name' => __( 'Vendor Unsuspended Email Contents', 'edd_fes' ),
				'desc' => __( 'The email body for the vendor unsuspended email. A list of email tags you can use is ', 'edd_fes' ).'<a href="http://docs.easydigitaldownloads.com/article/338-frontend-submissions-email-configuration" target="_blank" >'.__( ' in our documentation', 'edd_fes' ).'</a>.',
				'std' =>  'Dear {firstname},
	Your vendor account has been reinstated.',
			),
			'fes-vendor-new-submission-email-toggle' => array(
				'id'=> 'fes-vendor-new-submission-email-toggle',
				'type' => 'checkbox',
				'name' => __( 'New Submission', 'edd_fes' ),
				'desc' => __( 'Check this box to email a vendor when he or she submits a new product and the "Automatically Approve Submissions" setting is off.', 'edd_fes' ),
			),
			'fes-vendor-new-submission-email' => array(
				'id' => 'fes-vendor-new-submission-email',
				'type' => 'rich_editor',
				'name' => __( 'New Submission Email Contents', 'edd_fes' ),
				'desc' => __( 'The email body for the new submission email. A list of email tags you can use is ', 'edd_fes' ).'<a href="http://docs.easydigitaldownloads.com/article/338-frontend-submissions-email-configuration" target="_blank" >'.__( ' in our documentation', 'edd_fes' ).'</a>.',
				'std' =>  'Dear {firstname},
	Your submission, {post-title}, has been received.

	It will be reviewed as soon as possible.',
			),
			'fes-vendor-submission-approved-email-toggle' => array(
				'id'=> 'fes-vendor-submission-approved-email-toggle',
				'type' => 'checkbox',
				'name' => __( 'Submission Approved', 'edd_fes' ),
				'desc' => __( 'Check this box to email a vendor when his or her product submission is approved and the "Automatically Approve Submissions" setting is off.', 'edd_fes' ),
			),
			'fes-vendor-submission-approved-email' => array(
				'id' => 'fes-vendor-submission-approved-email',
				'type' => 'rich_editor',
				'name' => __( 'Submission Approved Email Contents', 'edd_fes' ),
				'desc' => __( 'The email body for the submission approved email. A list of email tags you can use is ', 'edd_fes' ).'<a href="http://docs.easydigitaldownloads.com/article/338-frontend-submissions-email-configuration" target="_blank" >'.__( ' in our documentation', 'edd_fes' ).'</a>.',
				'std' =>  'Dear {firstname},
	Your submission, {post-title}, has been approved!',
			),
			'fes-vendor-submission-declined-email-toggle' => array(
				'id'=> 'fes-vendor-submission-declined-email-toggle',
				'type' => 'checkbox',
				'name' => __( 'Submission Declined', 'edd_fes' ),
				'desc' => __( 'Check this box to email a vendor when his or her product submission is declined and the "Automatically Approve Submissions" setting is off.', 'edd_fes' ),
			),
			'fes-vendor-submission-declined-email' => array(
				'id' => 'fes-vendor-submission-declined-email',
				'type' => 'rich_editor',
				'name' => __( 'Submission Declined Email Contents', 'edd_fes' ),
				'desc' => __( 'The email body for the submission declined email. A list of email tags you can use is ', 'edd_fes' ).'<a href="http://docs.easydigitaldownloads.com/article/338-frontend-submissions-email-configuration" target="_blank" >'.__( ' in our documentation', 'edd_fes' ).'</a>.',
				'std' =>  'Dear {firstname},
	Your submission, {post-title}, has been declined.',
			),
			'fes-vendor-submission-revoked-email-toggle' => array(
				'id'=> 'fes-vendor-submission-revoked-email-toggle',
				'type' => 'checkbox',
				'name' => __( 'Submission Revoked', 'edd_fes' ),
				'desc' => __( 'Check this box to email a vendor when his or her product is revoked.', 'edd_fes' ),
			),
			'fes-vendor-submission-revoked-email' => array(
				'id' => 'fes-vendor-submission-revoked-email',
				'type' => 'rich_editor',
				'name' => __( 'Submission Revoked Email Contents', 'edd_fes' ),
				'desc' => __( 'The email body for the submission revoked email. A list of email tags you can use is ', 'edd_fes' ).'<a href="http://docs.easydigitaldownloads.com/article/338-frontend-submissions-email-configuration" target="_blank" >'.__( ' in our documentation', 'edd_fes' ).'</a>.',
				'std' =>  'Dear {firstname},
	Your submission, {post-title}, has been revoked.',
			),
		);
		$sections['integrations'] = array(
			'fes-recaptcha-public-key' => array(
				'id' => 'fes-recaptcha-public-key',
				'type' => 'text',
				'name' => __( 'reCAPTCHA Site key', 'edd_fes' ),
				'desc' => __( 'Create your reCAPTCHA keys ', 'edd_fes' ) . '<a href="https://www.google.com/recaptcha/admin#list" target="_blank">'. __( 'here', 'edd_fes' ) . '</a>.',
			),
			'fes-recaptcha-private-key' => array(
				'id' => 'fes-recaptcha-private-key',
				'type' => 'text',
				'name' => __( 'reCAPTCHA Secret key', 'edd_fes' ),
				'desc' => __( 'Create your reCAPTCHA keys ', 'edd_fes' ) . '<a href="https://www.google.com/recaptcha/admin#list" target="_blank">'. __( 'here', 'edd_fes' ) . '</a>.',
			),
			'fes-login-captcha' => array(
				'id'=> 'fes-login-captcha',
				'type' => 'checkbox',
				'name' => __( 'reCAPTCHA on the login form', 'edd_fes' ),
				'desc' => __( 'Check this box to show a reCAPTCHA field on the login form on the vendor dashboard.', 'edd_fes' ),
			),
			'fes-vendor-contact-captcha' => array(
				'id'=> 'fes-vendor-contact-captcha',
				'type' => 'checkbox',
				'name' => __( 'reCAPTCHA on the vendor contact form', 'edd_fes' ),
				'desc' => __( 'Check this box to show a reCAPTCHA field on the vendor contact form.', 'edd_fes' ),
			),
		);
		$settings['fes'] = $sections;
		return $settings;
	}

	/**
	 * FES Settings Panel Tab.
	 *
	 * Makes a tab for FES on the EDD settings panel.
	 *
	 * @since 2.4.0
	 * @access public
	 *
	 * @param  array $tabs EDD settings panel tabs.
	 * @return array EDD settings panel tabs.
	 */
	public function edd_settings_panel_add_tab( $tabs ) {
		$tabs['fes'] = __( 'FES', 'edd_fes' );
		return $tabs;
	}

	/**
	 * FES Settings Panel.
	 *
	 * Makes sections on the FES settings tab
	 * on the EDD settings panel.
	 *
	 * @since 2.4.0
	 * @access public
	 *
	 * @param  array $sections EDD settings panel sections.
	 * @return array EDD settings panel sections.
	 */
	public function edd_settings_panel_add_sections( $sections ) {
		$sections['fes']['main'] = __( 'Main Settings', 'edd_fes' );
		$sections['fes']['forms'] = __( 'Forms/Pages', 'edd_fes' );
		$sections['fes']['permissions'] = __( 'Permissions', 'edd_fes' );
		$sections['fes']['emails'] = __( 'Emails', 'edd_fes' );
		$sections['fes']['integrations'] = __( 'Integrations', 'edd_fes' );
		return $sections;
	}
}