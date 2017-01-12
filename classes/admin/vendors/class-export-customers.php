<?php
/**
 * Customers Export
 *
 * This class handles customer export
 *
 * @package FES
 * @subpackage Reports
 * @since 2.3.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { 
	exit;
}

// Exit if EDD Export class doesn't exist
if ( ! class_exists( 'EDD_Export' ) ){
	exit;
}

/**
 * FES Customers Export Class
 *
 * This class extends the EDD_Export class
 * to allow for CSV exports of a vendor's 
 * customers.
 *
 * @since 2.3.0
 * @access public
 */
class FES_Customers_Export extends EDD_Export {

	/**
	 * The type of export. 
	 *
	 * @since 2.3.0
	 * @access public
	 * @var string $export_type Export type. Used for export-type specific filters/actions.
	 */	
	public $export_type = 'customers';

	/**
	 * Set the export headers.
	 * 
	 * Sets the headers for PHP for the CSV export.
	 *
	 * @since 2.3.0
	 * @access public
	 * 
	 * @return void
	 */
	public function headers() {
		ignore_user_abort( true );

		if ( ! edd_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
			set_time_limit( 0 );
		}

		$extra = '';

		if ( ! empty( $_POST['fes_export_download'] ) ) {
			$extra = sanitize_title( get_the_title( absint( $_POST['fes_export_download'] ) ) ) . '-';
		}

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . apply_filters( 'edd_customers_export_filename', 'edd-export-' . $extra . $this->export_type . '-' . date( 'm-d-Y' ) ) . '.csv' );
		header( "Expires: 0" );
	}

	/**
	 * Sets the CSV columns.
	 * 
	 * Sets the CSV columns for the CSV export.
	 *
	 * @since 2.3.0
	 * @access public
	 * 
	 * @return array All the columns.
	 */	
	public function csv_cols() {
		if ( ! empty( $_POST['fes_export_download'] ) ) {
			$cols = array(
				'product_id'     => sprintf( _x( '%s ID', 'FES singular uppercase setting for download', 'edd_fes' ), EDD_FES()->helper->get_product_constant_name( $plural = false, $uppercase = true ) ),
				'product_title'  => sprintf( _x( '%s Title', 'FES singular uppercase setting for download', 'edd_fes' ), EDD_FES()->helper->get_product_constant_name( $plural = false, $uppercase = true ) ),
				'option_id'      => sprintf( _x( '%s Option ID', 'FES singular uppercase setting for download', 'edd_fes' ), EDD_FES()->helper->get_product_constant_name( $plural = false, $uppercase = true ) ),
				'option_title'   => sprintf( _x( '%s Option Title', 'FES singular uppercase setting for download', 'edd_fes' ), EDD_FES()->helper->get_product_constant_name( $plural = false, $uppercase = true ) ),				
				'first_name'     => __( 'First Name',   'edd_fes' ),
				'last_name'      => __( 'Last Name',   'edd_fes' ),
				'email'          => __( 'Email', 'edd_fes' ),
				'date'           => __( 'Date Purchased', 'edd_fes' )
			);
		} else {

			$cols = array(
				'product_id'     => sprintf( _x( '%s ID', 'FES singular uppercase setting for download', 'edd_fes' ), EDD_FES()->helper->get_product_constant_name( $plural = false, $uppercase = true ) ),
				'product_title'  => sprintf( _x( '%s Title', 'FES singular uppercase setting for download', 'edd_fes' ), EDD_FES()->helper->get_product_constant_name( $plural = false, $uppercase = true ) ),
				'option_id'      => sprintf( _x( '%s Option ID', 'FES singular uppercase setting for download', 'edd_fes' ), EDD_FES()->helper->get_product_constant_name( $plural = false, $uppercase = true ) ),
				'option_title'   => sprintf( _x( '%s Option Title', 'FES singular uppercase setting for download', 'edd_fes' ), EDD_FES()->helper->get_product_constant_name( $plural = false, $uppercase = true ) ),
			);

			if ( 'emails' != $_POST['fes_export_option'] ) {
				$cols['name'] = __( 'Name',   'edd_fes' );
			}

			$cols['email'] = __( 'Email',   'edd_fes' );

			if ( 'full' == $_POST['fes_export_option'] ) {
				$cols['purchases'] = __( 'Total Purchases',   'edd_fes' );
				$cols['amount']    = __( 'Total Purchased', 'edd_fes' ) . ' (' . html_entity_decode( edd_currency_filter( '' ) ) . ')';
			}

		}

		return $cols;
	}

	/**
	 * Get the Export Data.
	 *
	 * Gets the data used in the CSV export of the 
	 * customers.
	 *
	 * @access public
	 * @since 2.3.0
	 * 
	 * @global object $wpdb Used to query the database using the WordPress
	 *   Database API
	 * @global object $edd_logs EDD Logs Object
	 * 
	 * @return array The data for the CSV file
	 */
	public function get_data() {
		global $wpdb;

		$data = array();

		if ( ! empty( $_POST['fes_export_download'] ) ) {

			// Export customers of a specific product
			global $edd_logs;
			$post = get_post( $_POST['fes_export_download'] );

			$args = array(
				'post_parent' => absint( $_POST['fes_export_download'] ),
				'log_type'    => 'sale',
				'nopaging'    => true
			);

			if ( isset( $_POST['edd_price_option'] ) ) {
				$args['meta_query'] = array(
					array(
						'key'   => '_edd_log_price_id',
						'value' => (int) $_POST['edd_price_option']
					)
				);
			}

			$logs = $edd_logs->get_connected_logs( $args );

			if ( $logs ) {
				foreach ( $logs as $log ) {
					$payment_id = get_post_meta( $log->ID, '_edd_log_payment_id', true );
					$user_info  = edd_get_payment_meta_user_info( $payment_id );

					$cart_details   = edd_get_payment_meta_cart_details( $payment_id );

					$option_id    = __( 'N/A', 'edd_fes' );
					$option_title = __( 'N/A', 'edd_fes' );
					foreach ( $cart_details as $cart_index => $download ) {
						if ( $download['id'] != $_POST['fes_export_download'] ){
							continue;
						}
						$download_type = edd_get_download_type( $download['id'] );
						$id      = isset( $download['item_number']['options']['price_id'] ) ? (int) $download['item_number']['options']['price_id'] : false;
						if ( $id ){
							$option_title = edd_get_price_option_name( $download['id'], $id );
							$option_id    = $id;
							break;
						}
					}

					$data[] = array(
						'product_id'    => $post->ID,
						'product_title' => $post->post_title,
						'option_id'		=> $option_id,
						'option_title'  => $option_title,
						'first_name'    => $user_info['first_name'],
						'last_name'     => $user_info['last_name'],
						'email'         => $user_info['email'],
						'date'          => $log->post_date
					);
				}
			}

		} else {
			$vendor = new FES_Vendor( $_REQUEST['id'] );
			$products = EDD_FES()->vendors->get_all_products( $vendor->user_id );
			$arr = array();
			if ( empty ( $products ) ){
				return;
			}
			foreach( $products as $product ){
				$arr[] = $product['ID'];
			}

			foreach( $arr as $product ){
				// Export customers of a specific product
				global $edd_logs;
				$post = get_post( $product );
				$args = array(
					'post_parent' => absint( $product ),
					'log_type'    => 'sale',
					'nopaging'    => true
				);

				$logs = $edd_logs->get_connected_logs( $args );

				if ( $logs ) {
					foreach ( $logs as $log ) {
						$payment_id = get_post_meta( $log->ID, '_edd_log_payment_id', true );
						$user_info  = edd_get_payment_meta_user_info( $payment_id );
						$cart_details   = edd_get_payment_meta_cart_details( $payment_id );

						$option_id    = __( 'N/A', 'edd_fes' );
						$option_title = __( 'N/A', 'edd_fes' );
						foreach ( $cart_details as $cart_index => $download ) {
							if ( $download['id'] != $product ){
								continue;
							}
							$download_type = edd_get_download_type( $download['id'] );
							$id      = isset( $download['item_number']['options']['price_id'] ) ? (int) $download['item_number']['options']['price_id'] : false;
							if ( $id ){
								$option_title = edd_get_price_option_name( $download['id'], $id );
								$option_id    = $id;
								break;
							}
						}
						$data[] = array(
							'product_id'    => $product,
							'product_title' => $post->post_title,
							'option_id'		=> $option_id,
							'option_title'  => $option_title,
							'first_name'    => $user_info['first_name'],
							'last_name'     => $user_info['last_name'],
							'email'         => $user_info['email'],
							'date'          => $log->post_date
						);
					}
				}
			}

		}

		/**
		 * Export CSV data.
		 *
		 * Allows for the data for the CSV export
		 * to be altered.
		 *
		 * @since 2.3.0
		 *
		 * @param array $data Data for the export.
		 */
		$data = apply_filters( 'fes_export_get_data', $data );

		/**
		 * Customer CSV data.
		 *
		 * Allows for the data for the CSV customer export
		 * to be altered.
		 *
		 * @since 2.3.0
		 *
		 * @param array $data Data for the export.
		 */
		$data = apply_filters( 'fes_export_get_data_' . $this->export_type, $data );

		return $data;
	}
}