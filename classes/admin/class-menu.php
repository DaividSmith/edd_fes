<?php
/**
 * FES Menu
 *
 * This file deals with FES's menu items.
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
 * FES Menu.
 *
 * Creates all of the menu and submenu
 * items FES adds to the backend.
 *
 * @since 2.3.0
 * @access public
 */
class FES_Menu {

	/**
	 * FES Menu Actions.
	 *
	 * Runs actions required to add
	 * menus and submenus.
	 *
	 * @since 2.3.0
	 * @access public
	 * 
	 * @return void
	 */	
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menus' ), 9 );
	}

	/**
	 * FES Menu Items.
	 *
	 * Adds the menu and submenu pages.
	 *
	 * @since 2.3.0
	 * @access public
	 * 
	 * @return void
	 */	
	public function admin_menus() {
		if ( EDD_FES()->vendors->user_is_admin() ){
			$welcome    = new FES_Welcome();
			$tools      = new FES_Tools();
			$minimum_capability = 'manage_shop_settings';
			add_menu_page(  __( 'EDD FES', 'edd_fes' ), __( 'EDD FES', 'edd_fes' ), $minimum_capability, 'fes-about', array( $welcome, 'load_page' ), '', '25.01' );
			add_submenu_page( 'fes-about', __( 'About', 'edd_fes' ), __( 'About', 'edd_fes' ), $minimum_capability, 'fes-about', array( $welcome, 'load_page' ) );
			add_submenu_page( 'fes-about', EDD_FES()->helper->get_vendor_constant_name( $plural = true, $uppercase = true ), EDD_FES()->helper->get_vendor_constant_name( $plural = true, $uppercase = true ), 'manage_shop_settings', 'fes-vendors', 'fes_vendors_page' );
			foreach ( EDD_FES()->load_forms as $name => $class ) {
				$form = new $class( $name, 'name' );
				if ( $form->has_formbuilder() && ! empty( $form->id ) ) {
					add_submenu_page( 'fes-about', $form->title( true ), $form->title( true ), 'manage_shop_settings', 'post.php?post=' . $form->id . '&action=edit' );
				}
			}
			add_submenu_page( 'fes-about', __( 'Tools', 'edd_fes' ), __( 'Tools', 'edd_fes' ), 'manage_shop_settings', 'fes-tools', array( $tools, 'fes_tools_page' ) );
			add_submenu_page( 'fes-about', __( 'Settings', 'edd_fes' ), __( 'Settings', 'edd_fes' ), 'manage_shop_settings', 'edit.php?post_type=download&page=edd-settings&tab=fes', 'edd_options_page' );
		}
	}
}
