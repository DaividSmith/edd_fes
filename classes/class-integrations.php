<?php
/**
 * FES Integrations
 *
 * This file contains integration code.
 *
 * @package FES
 * @subpackage Integrations
 * @since 2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FES Integrations.
 *
 * Contains code to integrate FES with other
 * EDD extensions.
 *
 * @since 2.0.0
 * @access public
 */
class FES_Integrations {

	/**
	 * FES integrations construct.
	 *
	 * Enables review support for the download
	 * post type.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @return void
	 */
	function __construct() {
		add_filter( 'edd_download_supports', array( $this, 'enable_reviews' ) );
		add_filter( 'edd_img_wtm_is_download', array( $this, 'diw_set_is_download' ) );
	}

	/**
	 * Is commissions active?
	 *
	 * Is the EDD Commissions plugin
	 * active?
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @return bool Whether or not Commissions is active.
	 */
	public function is_commissions_active() {
		if ( ! defined( 'EDDC_PLUGIN_DIR' ) ) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * FES enable reviews.
	 *
	 * Enables review support for the download
	 * post type.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param array $supports Array of post type supported items.
	 * @return array Array of post type supported items.
	 */
	public function enable_reviews( $supports ) {
		return array_merge( $supports, array( 'reviews' ) );
	}

	/**
	 * Instructs Download Image Watermark that files uploaded during new product submissions are downloads
	 *
	 * @since 2.4.2
	 * @access public
	 */
	public function diw_set_is_download( $is_download ) {

		if( EDD()->session->get( 'fes_is_new' ) ) {
			$is_download = true;
		}

		return $is_download;
	}
}