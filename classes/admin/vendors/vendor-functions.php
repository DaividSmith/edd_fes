<?php
/**
 * Admin Vendor Registration Functions.
 *
 * This file contains functions used to
 * register views and tabs on the vendor
 * profile pages in the admin.
 *
 * @package FES
 * @subpackage Administration
 * @since 2.3.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { 
	exit;
}

/**
 * Register a view for the single vendor view.
 *
 * This function basically maps the tabs to 
 * the functions to call to render the vendor
 * profile page tabs.
 *
 * @since 2.3.0
 * @access public
 *
 * @param array $views An array of existing views.
 * @return array The altered list of views.
 */
function fes_register_default_vendor_views( $views ) {

	$default_views = array(
		'overview'     => 'fes_vendors_view',
		'notes'        => 'fes_vendor_notes_view',
		'registration' => 'fes_vendor_registration_view',
		'profile'      => 'fes_vendor_profile_view',
		'products'     => 'fes_vendor_products_view',
		'commissions'  => 'fes_vendor_commissions_view',
		'reports'      => 'fes_vendor_reports_view',
		'exports'      => 'fes_vendor_exports_view'
	);

	if ( !EDD_FES()->integrations->is_commissions_active() ){
		unset( $default_views['commissions'] );
	}

	return array_merge( $views, $default_views );

}
add_filter( 'fes_vendor_views', 'fes_register_default_vendor_views', 1, 1 );

/**
 * Register a tab for the single vendor view.
 *
 * This function basically contains the array
 * of tabs and the icons and labels to be 
 * shown for them.
 *
 * @since 2.3.0
 * @access public
 *
 * @param array $tabs An array of existing tabs.
 * @return array The altered list of tabs
 */
function fes_register_default_vendor_tabs( $tabs ) {

	$default_tabs = array(
		'overview'       => array( 'dashicon' => 'dashicons-admin-users',    'title' => sprintf( __( '%s Overview', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = true ) ) ),
		'notes'          => array( 'dashicon' => 'dashicons-admin-comments', 'title' => sprintf( __( '%s Notes', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = true ) ) ),
		'registration'   => array( 'dashicon' => 'dashicons-index-card',     'title' => sprintf( __( '%s Registration', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = true ) ) ),
		'profile'        => array( 'dashicon' => 'dashicons-admin-settings', 'title' => sprintf( __( '%s Profile', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = true ) ) ),
		'products'       => array( 'dashicon' => 'dashicons-admin-page',     'title' => sprintf( _x( '%1s %2s', 'FES vendor uppercase singular, FES product uppercase singular', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = true ), EDD_FES()->helper->get_product_constant_name( $plural = true, $uppercase = true ) ) ),
		'commissions'    => array( 'dashicon' => 'dashicons-products', 		 'title' => sprintf( __( '%s Commissions', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = true ) ) ),
		'reports'        => array( 'dashicon' => 'dashicons-chart-line',     'title' => sprintf( __( '%s Reports', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = true ) )),
		'exports'        => array( 'dashicon' => 'dashicons-download', 		 'title' => sprintf( __( '%s Exports', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = true ) ) ),
	);

	if ( !EDD_FES()->integrations->is_commissions_active() ){
		unset( $default_tabs['commissions'] );
	}

	return array_merge( $tabs, $default_tabs );
}
add_filter( 'fes_vendor_tabs', 'fes_register_default_vendor_tabs', 1, 1 );
