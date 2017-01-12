<?php
/**
 * FES Vendor Shop
 *
 * This file deals with with vendor shop page.
 *
 * @package FES
 * @subpackage Frontend
 * @since 2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { 
	exit;
}

/**
 * FES Vendor Shop Page.
 *
 * This class creates the vendor page
 * with each vendor's products on it.
 *
 * @since 2.0.0
 * @access public
 */
class FES_Vendor_Shop {
	/**
	 * FES Vendor Shop Actions and Filters.
	 *
	 * Registers actions and filters used to make
	 * the vendor shop pages.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @return void
	 */	
	public function __construct() {
		add_action( 'pre_get_posts', array( $this, 'vendor_download_query' ) );
		add_action( 'the_content', array( $this, 'content' ), 10 );
		add_filter( 'init', array( $this, 'add_rewrite_rules' ),0 );
		add_filter( 'query_vars', array( $this, 'query_vars' ), 0 );
		add_filter( 'the_title',  array( $this, 'change_the_title' ), 11, 2 );
		add_action( 'save_post', array( $this, 'vendor_page_updated' ), 10, 1 );
		add_action( 'admin_init', array( $this, 'after_vendor_page_update' ), 10 );
		add_filter( 'wp_title',  array( $this, 'wp_title' ), 11, 1 );
		add_filter( 'pre_get_document_title',  array( $this, 'wp_title' ), 11, 1 );
	}

	/**
	 * FES Vendor Shop Page content
	 *
	 * Creates the content shown on the vendor
	 * shop page.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param string $content Content of the page/post being rendered.
	 * @return string Content to display on page.
	 */	
	public function content( $content ) {

		$has_shortcode = false;

		if ( function_exists( 'has_shortcode' ) ) {
			$has_shortcode = has_shortcode( $content, 'downloads' );
		}

		if ( $this->get_queried_vendor() && ! $has_shortcode ) {
			return do_shortcode( '[downloads]' );
		} else {
			return $content;
		}

	}

	/**
	 * Vendor Shop Query Vars.
	 *
	 * Registers the vendor query arg for 
	 * use in making the vendor shop page.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param array $query_vars Query vars already registered.
	 * @return array Query vars registered in WordPress.
	 */	
	public function query_vars( $query_vars ) {
		$query_vars[] = 'vendor';
		return $query_vars;
	}

	/**
	 * Vendor Shop Rewrite Rules.
	 *
	 * Makes the rewrite rules used by FES
	 * to make pretty permalinks for the vendor
	 * store pages.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function add_rewrite_rules() {

		if ( ! EDD_FES()->helper->get_option( 'fes-vendor-page', false ) ) {
			return;
		}

		$page_id   = EDD_FES()->helper->get_option( 'fes-vendor-page', false );
		$page      = get_page( $page_id );
		$page_name = ! empty( $page->post_name ) ? $page->post_name : EDD_FES()->helper->get_vendor_constant_name( false, false );
		$url 	   = untrailingslashit( $page_name );
		/**
		 * Vendor Shop Page URL.
		 *
		 * Adjusts the default permalink to the vendor shop page.
		 *
		 * @since 2.0.0
		 *
		 * @param  string $url Default vendor url.
		 */			
		$permalink = apply_filters( 'fes_adjust_vendor_url', $url );

		// Remove beginning slash
		if ( substr( $permalink, 0, 1 ) == '/' ) {
			$permalink = substr( $permalink, 1, strlen( $permalink ) );
		}

		add_rewrite_rule("{$page_name}/([^/]+)/page/?([2-9][0-9]*)", "index.php?page_id={$page_id}&vendor=\$matches[1]&paged=\$matches[2]", 'top');
		add_rewrite_rule("{$page_name}/([^/]+)", "index.php?page_id={$page_id}&vendor=\$matches[1]", 'top');
	}

	/**
	 * Retrieves the currently displayed vendor.
	 *
	 * This is used when display a vendor's store page.
	 *
	 * @since 2.2.10
	 * @access public
	 *
	 * @global $wp_query Check to make sure the query
	 *         			 object is an object, else return.
	 *         			 See #974.
	 * 
	 * @return object|false WP User Object or false.
	 */
	public function get_queried_vendor() {

		global $wp_query;

		if( ! is_object( $wp_query ) ) {
			return false;
		}

		$user   = false;
		$vendor = get_query_var( 'vendor' );

		if ( ! empty( $vendor ) ) {
			if ( is_numeric( $vendor ) ) {
				$user = get_userdata( absint( $vendor ) );
			} else {
				$user = get_user_by( 'slug', $vendor );
			}
		}
		return $user;
	}

	/**
	 * Vendor Products Query.
	 *
	 * Filters the download shortcode
	 * used to make the vendor products show
	 * up on the vendor pages.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param  array $query Unused.
	 * @return void
	 */
	public function vendor_download_query( $query ) {

		if ( is_admin() ) {
			return;
		}

		if ( $this->get_queried_vendor() ) {
			add_filter( 'edd_downloads_query', array( $this, 'set_shortcode' ) );
		}
	}

	/**
	 * Vendor Set Shortcode.
	 *
	 * Tells the download shortcode to
	 * only get downloads by the vendor.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param  array $query Query array arguments.
	 * @return array Query array arguments for the download
	 *               shortcode.
	 */
	public function set_shortcode( $query ) {

		$vendor = $this->get_queried_vendor();

		if ( $vendor ) {
			$query['author'] = $vendor->ID;
		}
		return $query;
	}

	/**
	 * Vendor Page Title.
	 *
	 * Changes the title of the vendor store
	 * page to a custom title.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param  string $title Existing title of page.
	 * @param  int $id Post id of page.
	 * @return string New title of page.
	 */
	public function change_the_title( $title, $id = null ) {
		$vendor_page = EDD_FES()->helper->get_option( 'fes-vendor-page', false );
		if ( ! is_page( $vendor_page ) || $id != $vendor_page || is_admin() ) {
			// if this is not the vendor page
			return $title;
		} else {
			$vendor = $this->get_queried_vendor();
			if ( ! $vendor ) {
				$title = EDD_FES()->helper->get_product_constant_name( $plural = true, $uppercase = true ); 
			} else {
				$store_name = get_user_meta( $vendor->ID, 'name_of_store', true );
				if ( empty( $store_name ) ) {
					$vendor_name = EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = true ) . ' ' . $vendor->display_name;
					$title = sprintf( __('The Shop of %s','edd_fes'), $vendor_name );
				} else {
					$title = $store_name;
				}
			}
			$id    = !empty( $vendor->ID ) ? $vendor->ID : 0;
			/**
			 * Vendor Shop Page Title.
			 *
			 * Adjusts the default title to the vendor shop page.
			 *
			 * @since 2.0.0
			 *
			 * @param  string $title Default vendor title.
			 * @param  int $id Vendor ID.
			 */				
			$title = apply_filters( 'fes_change_the_title', $title , $id );
			remove_filter( 'the_title', array( $this, 'change_the_title' ) );
			return $title;
		}
	}

	public function wp_title( $title ) {
		$vendor_page = EDD_FES()->helper->get_option( 'fes-vendor-page', false );
		if ( ! is_page( $vendor_page ) || is_admin() ) {
			// if this is not the vendor page
			return $title;
		} else {
			$vendor = $this->get_queried_vendor();
			if ( ! $vendor ) {
				$title = EDD_FES()->helper->get_product_constant_name( $plural = true, $uppercase = true ); 
			} else {
				$store_name = get_user_meta( $vendor->ID, 'name_of_store', true );
				if ( empty( $store_name ) ) {
					$vendor_name = EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = true ) . ' ' . $vendor->display_name;
					$title = sprintf( __('The Shop of %s','edd_fes'), $vendor_name );
				} else {
					$title = $store_name;
				}
			}
			remove_filter( 'wp_title', array( $this, 'wp_title' ) );
			return $title;
		}
	}

	/**
	 * Vendor Page Updated.
	 *
	 * When the vendor page is updated, refresh the 
	 * rewrite rules.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @see  FES_Vendor_Shop::after_vendor_page_update()
	 *
	 * @param  int $post_id Post id of page.
	 * @return int Post id of page.
	 */
	public function vendor_page_updated( $post_id ) {

		if ( ! EDD_FES()->helper->get_option( 'fes-vendor-page', false ) ) {
			return;
		}

		$page_id = EDD_FES()->helper->get_option( 'fes-vendor-page', false );

		if ( (int) $page_id !== (int) $post_id ) {
			return;
		}

		$this->add_rewrite_rules();

		// Set an option so we know to flush the rewrites at the next admin_init
		add_option( 'fes_permalinks_updated', 1, '', 'no' );

		return $post_id;
	}

	/**
	 * After Vendor Page Updated.
	 *
	 * When the vendor page is updated, refresh the 
	 * rewrite rules.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @see  FES_Vendor_Shop::vendor_page_updated()
	 * 
	 * @return void
	 */
	public function after_vendor_page_update() {

		$fes_permalinks_updated = get_option( 'fes_permalinks_updated' );

		if ( empty( $fes_permalinks_updated ) ) {
			return;
		}

		flush_rewrite_rules();

		delete_option( 'fes_permalinks_updated' );

	}
}
