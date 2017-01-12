<?php
/**
 * FES Post Types
 *
 * This file contains code that affects the 
 * FES Forms post type.
 *
 * @package FES
 * @subpackage Post Types
 * @since 2.3.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { 
	exit;
}

/**
 * FES Post Types.
 *
 * This file contains code that affects the 
 * FES Forms post type.
 *
 * @since 2.3.0
 * @access public
 */
class FES_Post_Types {

	/**
	 * FES Post Types action/filters.
	 *
	 * Registers the actions and filters to create
	 * the FES Forms post type and disable UI items 
	 * of it.
	 *
	 * @since 2.3.0
	 * @access public
	 * 
	 * @return void
	 */
	public function __construct() {
		add_action( 'init',  array( $this, 'register_post_types' ) );
		add_filter( "bulk_actions-edit-fes-forms", '__return_empty_array' );
		add_filter( 'disable_months_dropdown', array( $this, 'fes_disable_months_dropdown'), 10, 2 );
	}

	/**
	 * Register FES Forms post type.
	 *
	 * Adds the FES Forms post type which is 
	 * used to store FES forms.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @return void
	 */	
	public function register_post_types() {
		$capability = 'manage_shop_settings';
		register_post_type( 'fes-forms', array(
			'label' => __( 'EDD FES Forms', 'edd_fes' ),
			'public' => false,
			'rewrites' => false,
			'capability_type' => 'post',
			'capabilities' => array(
				'publish_posts' => 'cap_that_doesnt_exist',
				'edit_posts' => $capability,
				'edit_others_posts' => $capability,
				'delete_posts' => 'cap_that_doesnt_exist',
				'delete_others_posts' => 'cap_that_doesnt_exist',
				'read_private_posts' => 'cap_that_doesnt_exist',
				'edit_post' => $capability,
				'delete_post' => 'cap_that_doesnt_exist',
				'read_post' => $capability,
				'create_posts' => 'cap_that_doesnt_exist'
			),
			'hierarchical' => false,
			'query_var' => false,
			'supports' => array(
				'title'
			),
			'can_export'  => true,
			'show_ui'           => true,
			'show_in_menu' => false,
			'show_in_nav_menus' => false,
			'show_in_admin_bar' => false,
			'labels' => array(
				'name' => __( 'EDD FES Forms', 'edd_fes' ),
				'singular_name' => __( 'FES Form', 'edd_fes' ),
				'menu_name' => __( 'FES Forms', 'edd_fes' ),
				'add_new' => __( 'Add FES Form', 'edd_fes' ),
				'add_new_item' => __( 'Add New Form', 'edd_fes' ),
				'edit' => __( 'Edit', 'edd_fes' ),
				'edit_item' => '',
				'new_item' => __( 'New FES Form', 'edd_fes' ),
				'view' => __( 'View FES Form', 'edd_fes' ),
				'view_item' => __( 'View FES Form', 'edd_fes' ),
				'search_items' => __( 'Search FES Forms', 'edd_fes' ),
				'not_found' => __( 'No FES Forms Found', 'edd_fes' ),
				'not_found_in_trash' => __( 'No FES Forms Found in Trash', 'edd_fes' ),
				'parent' => __( 'Parent FES Form', 'edd_fes' )
			)
		) );
	}

	/**
	 * FES Disable Month Dropdown.
	 *
	 * On the list table of the FES
	 * Forms post type, remove the 
	 * dropdown for month created, since 
	 * that doesn't make any sense for use
	 * with FES.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @param  bool $hide Whether to hide the dropdown for the post type.
	 * @param  string $post_type The post type.
	 * @return bool Whether to hide the dropdown for the post type.
	 */	
	public function fes_disable_months_dropdown( $hide, $post_type ){
		if ( $post_type === 'fes-forms' ){
			return true;
		} else { 
			return $hide;
		}
	}
}
$post_types = new FES_Post_Types();
