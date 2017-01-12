<?php
/**
 * Vendor Table
 *
 * This file contains the extension of
 * the WP_List_Table to make the vendor
 * list table.
 *
 * @package FES
 * @subpackage Vendors
 * @since 2.2.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { 
	exit;
}

/**
 * FES Vendor Table Class.
 *
 * This class extends the WP_List_Table class
 * in WordPress core with the intention of providing
 * a vendor list table.
 *
 * @since 2.2.0
 * @access public
 */
class FES_Vendor_Table extends WP_List_Table {

	/**
	 * Number of results to show per page
	 *
	 * @since 2.2.0
	 * @access public
	 * @var int $per_page The number of vendors to show in a page.
	 */	
	public $per_page = 10;

	/**
	 * Total number of vendors for current view
	 *
	 * @since 2.2.0
	 * @access public
	 * @var int $total The number of vendors that match the view's conditions.
	 */
	public $total = 0;

	/**
	 * Vendor Table construct.
	 *
	 * Registers all actions and filters for the vendor table screen.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @global $status This filters the view for the vendor status.
	 * @global $page This is the pagination variable.
	 *
	 * @return void
	 */
	function __construct() {
		global $status, $page;
		parent::__construct( array(
			'singular'  => EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = false ),
			'plural'    => EDD_FES()->helper->get_vendor_constant_name( $plural = true, $uppercase = false ),
			'ajax'      => false // does this table support ajax?
		) );
	}

	/**
	 * Show the search field.
	 *
	 * This function provides the HTML used to make the search
	 * field on the vendor table.
	 *
	 * @since 2.2
	 * @access public
	 *
	 * @param string $text     Label for the search box.
	 * @param string $input_id ID of the search box.
	 *
	 * @return void
	 */
	function search_box( $text, $input_id ) {
		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['order'] ) ) {
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		} ?>
		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
			<input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" />
			<?php submit_button( $text, 'button', false, false, array( 'ID' => 'search-submit' ) ); ?>
		</p>
		<?php
	}

	/**
	 * Vendor table row.
	 *
	 * This function makes the values for the columns
	 * in the vendor table.
	 *
	 * @since 2.2
	 * @access public
	 *
	 * @param string $vendor Vendor object for the current row.
	 * @param string $column_name ID of the search box.
	 *
	 * @return mixed Data for the column.
	 */
	function column_default( $vendor, $column_name ) {
		$data = '';
		switch ( $column_name ) {
			case 'name':
				$data .= '<a href="'.admin_url( 'admin.php?page=fes-vendors&view=overview&id='.$vendor['id'] ).'">'.$vendor['name'].' ('.$vendor['username'].')</a>';
				if ( EDD_FES()->vendors->user_is_admin() ) {
					$admin_actions = array();
					$data .= '<br />';
					$vendor_id = $vendor['id'];
					if ( EDD_FES()->vendors->vendor_is_status( 'approved', $vendor_id ) ) {
						$admin_actions['view']   = array(
							'action' => 'view',
							'name' => __( 'View', 'edd_fes' ),
							'url' => admin_url( 'admin.php?page=fes-vendors&view=overview&id='.$vendor['id'] )
						);
						$admin_actions['revoke'] = array(
							'action' => 'revoked',
							'name' => sprintf( __( 'Revoke (and delete all %s of)', 'edd_fes' ), EDD_FES()->helper->get_product_constant_name( $plural = true, $uppercase = false ) ),
							'url' => '#'
						);
						$admin_actions['suspend'] = array(
							'action' => 'suspended',
							'name' => __( 'Suspend', 'edd_fes' ),
							'url' => '#'
						);
					} else if ( EDD_FES()->vendors->vendor_is_status( 'pending', $vendor_id ) ) {
						$admin_actions['view']   = array(
							'action' => 'view',
							'name' => __( 'View', 'edd_fes' ),
							'url' => admin_url( 'admin.php?page=fes-vendors&view=overview&id='.$vendor['id'] .'&action=edit' )
						);
						$admin_actions['approve'] = array(
							'action' => 'approved',
							'name' => __( 'Approve', 'edd_fes' ),
							'url' => '#'
						);
						$admin_actions['decline'] = array(
							'action' => 'declined',
							'name' => __( 'Decline', 'edd_fes' ),
							'url' => '#'
						);
					} else if ( EDD_FES()->vendors->vendor_is_status( 'suspended', $vendor_id ) ) {
						$admin_actions['view']   = array(
							'action' => 'view',
							'name' => __( 'View', 'edd_fes' ),
							'url' => admin_url( 'admin.php?page=fes-vendors&view=overview&id='.$vendor['id'] .'&action=edit' )
						);
						$admin_actions['revoke'] = array(
							'action' => 'revoked',
							'name' => sprintf( __( 'Revoke (and delete all %s of)', 'edd_fes' ), EDD_FES()->helper->get_product_constant_name( $plural = true, $uppercase = false ) ),
							'url' => '#'
						);
						$admin_actions['unsuspend'] = array(
							'action' => 'unsuspended',
							'name' => __( 'Unsuspend', 'edd_fes' ),
							'url' => '#'
						);
					} else {
						// not a vendor!
					}

					/**
					 * Vendor admin actions
					 *
					 * Contains an array of actions an administrator
					 * can take in regards to a vendor (like approve, reject
					 *  etc ).
					 *
					 * @since 2.3.0
					 *
					 * @param array $admin_actions Actions an administrator can take.
					 * @param array $vendor FES Vendor details (as array).
					 */
					$admin_actions = apply_filters( 'fes_admin_actions', $admin_actions, $vendor );
					// for each admin actions, make a button for it
					foreach ( $admin_actions as $action ) {
						$image = isset( $action['image_url'] ) ? $action['image_url'] : fes_plugin_url . 'assets/img/icons/' . $action['action'] . '.png';
						$class = $action['action'] === 'view' ? '' : 'vendor-change-status';
						$data .= sprintf( '<a class="button tips %s" data-vendor="%d" data-status="%s" data-nstatus="%s" href="%s" data-tip="%s"><img src="%s" alt="%s" width="14" /></a>', $class, (int) esc_attr( $vendor['user_id'] ), esc_attr( $action['action'] ), esc_attr( $action['name'] ), esc_url( $action['url'] ), esc_attr( $action['name'] ), esc_attr( $image ), esc_attr( $action['name'] ) );
					}
				}
				break;
			case 'products':
				$data = $vendor['product_count'];
				break;
			case 'status':
				if ( $vendor['status'] == 'pending' ) {
					$data = '<span class="download-status pending-review">' . __( 'Pending', 'edd_fes' ) . '</span>';
				} else if ( $vendor['status'] == 'approved' ) {
						$data = '<span class="download-status published">' . __( 'Approved', 'edd_fes' ) . '</span>';
					} else if ( $vendor['status'] == 'suspended' ) {
						$data = '<span class="download-status future">' . __( 'Suspended', 'edd_fes' ) . '</span>';
					} else {
					$data = __( 'Unknown Column', 'edd_fes' );
				}
				break;
			case 'date_created':
				$data = date_i18n( get_option( 'date_format' ), strtotime( $vendor['date_created'] ) );
				break;
			case 'sales_value':
				$data = edd_currency_filter( edd_format_amount( $vendor['sales_value'] ) );
				break;
			case 'sales_count':
				$data = $vendor['sales_count'];
				break;
			default:
				return print_r( $vendor, true ); // Show the whole array for troubleshooting purposes
				break;
		}
		/**
		 * Vendor table row contents
		 *
		 * Vendor table row contents as determined by 
		 * the switch case above.
		 *
		 * @since 2.2.0
		 *
		 * @param string $data The column contents.
		 */		
		return apply_filters( 'fes_vendor_table_value_' . $column_name, $data );
	}

	/**
	 * Vendor table checkbox.
	 *
	 * Makes the checkbox column for the
	 * vendor table.
	 *
	 * @since 2.2
	 * @access public
	 *
	 * @param array $vendor Vendor array for the current row.
	 * @return string HTML for the checkbox.
	 */
	function column_cb( $vendor ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ $this->_args['singular'],
			/*$2%s*/ $vendor['user_id']
		);
	}

	/**
	 * Vendor table columns.
	 *
	 * Column keys to their column titles on the
	 * vendor table.
	 *
	 * @since 2.2
	 * @access public
	 *
	 * @return array Key to title column relationships.
	 */
	function get_columns() {
		$columns = array(
			'cb'          => '<input type="checkbox" />', //Render a checkbox instead of text
			'name'        => __( 'Name (username)', 'edd_fes' ),
			'status'      => __( 'Status', 'edd_fes' ),
			'products'    => sprintf( _x( 'Number of %s', 'FES plural uppercase setting for download', 'edd_fes' ), EDD_FES()->helper->get_product_constant_name( $plural = true, $uppercase = true ) ),
			'sales_count' => __( 'Sales Count', 'edd_fes' ),
			'sales_value' => __( 'Sales Value', 'edd_fes' ),
			'date_created'=> sprintf( _x( '%s Since', 'FES plural uppercase setting for download', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = true ) ),
		);
		return $columns;
	}

	/**
	 * Vendor table views.
	 *
	 * Views of the vendor table (basically
	 * `all` and all of the current vendor
	 * statuses).
	 *
	 * @since 2.2
	 * @access public
	 *
	 * @return array Views and links to all of them.
	 */
	function get_views() {
		$base = admin_url( 'admin.php?page=fes-vendors' );
		$current = isset( $_GET['view'] ) ? sanitize_text_field( $_GET['view'] ) : 'all';
		$views = array(
			'all'       => sprintf( '<a href="%s"%s>%s <span class="count">(%s)</span></a>', add_query_arg( 'view', 'all', $base ), $current === 'all' ? ' class="current"' : '', __( 'All', 'edd_fes' ), $this->get_count( array( 'approved', 'suspended', 'pending' ) ) ),
			'pending'   => sprintf( '<a href="%s"%s>%s <span class="count">(%s)</span></a>', add_query_arg( 'view', 'pending', $base ), $current === 'pending' ? ' class="current"' : '', __( 'Pending', 'edd_fes' ), $this->get_count( array( 'pending' ) ) ),
			'approved'  => sprintf( '<a href="%s"%s>%s <span class="count">(%s)</span></a>', add_query_arg( 'view', 'approved', $base ), $current === 'approved' ? ' class="current"' : '', __( 'Approved', 'edd_fes' ), $this->get_count( array( 'approved' ) ) ),
			'suspended' => sprintf( '<a href="%s"%s>%s <span class="count">(%s)</span></a>', add_query_arg( 'view', 'suspended', $base ), $current === 'suspended' ? ' class="current"' : '', __( 'Suspended', 'edd_fes' ), $this->get_count( array( 'suspended' ) ) )
		);
		return $views;
	}

	/**
	 * Vendor table bulk actions.
	 *
	 * Creates the menu items for 
	 * all of the bulk actions an admin
	 * can do on the vendor table.
	 *
	 * @since 2.2
	 * @access public
	 *
	 * @return array Bulk actions array.
	 */
	function get_bulk_actions() {
		$actions = array(
			'approved'   => sprintf( __( 'Approve %s' , 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = true, $uppercase = true ) ),
			'declined'   => sprintf( __( 'Decline %s' , 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = true, $uppercase = true ) ),
			'revoked'    => sprintf( __( 'Revoke %s' , 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = true, $uppercase = true ) ),
			'suspended'   => sprintf( __( 'Suspend %s' , 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = true, $uppercase = true ) ),
			'unsuspended' => sprintf( __( 'Unsuspend %s' , 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = true, $uppercase = true ) ),
		);
		return $actions;
	}

	/**
	 * Retrieve the current page number.
	 *
	 * Gets the current page number
	 * a person is on while viewing
	 * the vendor table.
	 *
	 * @since 2.2
	 * @access public
	 *
	 * @return int Get current page of vendor table.
	 */
	function get_paged() {
		return isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
	}

	/**
	 * Retrieves the search query string.
	 *
	 * Returns the search query if one
	 * was run, else returns false on the 
	 * vendor table.
	 *
	 * @since 2.2
	 * @access public
	 *
	 * @return mixed If search term is present returns that, false otherwise.
	 */
	function get_search() {
		return ! empty( $_GET['s'] ) ? urldecode( trim( $_GET['s'] ) ) : false;
	}

	/**
	 * Vendor table offset.
	 * 
	 * Retrieve the offset based on the current page number.
	 *
	 * @since 2.2
	 * @access public
	 * 
	 * @return int The current offset.
	 */
	function get_offset() {
		$page = $this->get_paged();
		return $this->per_page * ( $page - 1 );
	}

	/**
	 * Vendor table vendor retrieve.
	 * 
	 * Based on the search term, offset,
	 * view and orderby retrieve the vendors
	 * to show in the vendor table.
	 *
	 * @since 2.2
	 * @since 2.3 can search by vendor meta (like PayPal email).
	 * @access public
	 * 
	 * @return array The vendors to show in the table.
	 */
	function get_vendors() {
		global $wpdb;

		$data    = array();
		$paged   = $this->get_paged();
		$offset  = $this->per_page * ( $paged - 1 );
		$search  = $this->get_search();
		$order   = isset( $_GET['order'] )   ? sanitize_text_field( $_GET['order'] )   : 'DESC';
		$orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'id';

		$args    = array(
			'number'  => $this->per_page,
			'offset'  => $offset,
			'order'   => $order,
			'orderby' => $orderby
		);

		if ( strpos( $search, ':' ) !== FALSE ) {
			global $wpdb;
			$pieces = explode( " ", $search );
			$user_by_meta = $wpdb->get_var(
				$wpdb->prepare( "
					SELECT user_id FROM $wpdb->usermeta
					WHERE meta_key = %s
					AND meta_value = %s
					LIMIT 1",
					sanitize_text_field( $pieces[0] ),
					sanitize_text_field( $pieces[1] )
				)
			);
			if ( $user_by_meta ) {
				$args['user_id']    = (int) $user_by_meta;
			}
		} else if ( is_email( $search ) ) {
				global $wpdb;
				$user_by_commissions = $wpdb->get_var(
					$wpdb->prepare( "
					SELECT user_id FROM $wpdb->usermeta
					WHERE meta_key = %s
					AND meta_value = %s
					LIMIT 1",
						'eddc_user_paypal',
						sanitize_email( $search )
					)
				);
				if ( $user_by_commissions ) {
					$args['user_id']    = (int) $user_by_commissions;
				}
				else {
					$args['email'] = $search;
				}
			} 
		else if ( is_numeric( $search ) ) {
			$args['user_id']    = $search;
		} else {
			$vendor = new FES_Vendor( $search );
			if ( $vendor && is_object( $vendor ) && $vendor->exists ) {
				$args['username']  = $search;
			} else {
				$args['name']  = $search;
			}
		}

		if ( isset( $_GET['view'] ) && $_GET['view'] !== 'all' ) {
			$args['status'] = $_GET['view'];
		}

		$vendors = new FES_DB_Vendors();
		$vendors = $vendors->get_vendors( $args );

		if ( $vendors ) {

			foreach ( $vendors as $vendor ) {

				$user_id = ! empty( $vendor->user_id ) ? intval( $vendor->user_id ) : 0;

				$data[] = array(
					'id'            => $vendor->id,
					'user_id'       => $user_id,
					'username'      => $vendor->username,
					'name'          => $vendor->name,
					'email'         => $vendor->email,
					'status'        => $vendor->status,
					'product_count' => $vendor->product_count,
					'sales_count'   => $vendor->sales_count,
					'sales_value'   => $vendor->sales_value,
					'date_created'  => $vendor->date_created,
				);
			}
		}
		return $data;
	}

	/**
	 * Vendor table pagination count.
	 * 
	 * Based on the search term, offset,
	 * view and orderby retrieve the number
	 * of matching vendors.
	 *
	 * @since 2.2
	 * @access public
	 *
	 * @todo Make this more efficient. Can we simply call
	 *       get_vendors() and just count() on the return?
	 * 
	 * @return int The number of matching vendors.
	 */
	function pagination_count() {
		global $wpdb;

		$data    = array();
		$search  = $this->get_search();

		if ( strpos( $search, ':' ) !== FALSE ) {
			global $wpdb;
			$pieces = explode( " ", $search );
			$user_by_meta = $wpdb->get_var(
				$wpdb->prepare( "
					SELECT user_id FROM $wpdb->usermeta
					WHERE meta_key = %s
					AND meta_value = %s
					LIMIT 1",
					sanitize_text_field( $pieces[0] ),
					sanitize_text_field( $pieces[1] )
				)
			);
			if ( $user_by_meta ) {
				$args['user_id']    = (int) $user_by_meta;
			}
		} else if ( is_email( $search ) ) {
				global $wpdb;
				$user_by_commissions = $wpdb->get_var(
					$wpdb->prepare( "
					SELECT user_id FROM $wpdb->usermeta
					WHERE meta_key = %s
					AND meta_value = %s
					LIMIT 1",
						'eddc_user_paypal',
						sanitize_email( $search )
					)
				);
				if ( $user_by_commissions ) {
					$args['user_id']    = (int) $user_by_commissions;
				}
				else {
					$args['email'] = $search;
				}
			} 
		else if ( is_numeric( $search ) ) {
			$args['user_id']    = $search;
		} else {
			$vendor = new FES_Vendor( $search );
			if ( $vendor && is_object( $vendor ) && $vendor->exists ) {
				$args['username']  = $search;
			} else {
				$args['name']  = $search;
			}
		}

		if ( isset( $_GET['view'] ) && $_GET['view'] !== 'all' ) {
			$args['status'] = $_GET['view'];
		}

		$vendors = new FES_DB_Vendors();
		$vendors = $vendors->count( $args );
		return $vendors;
	}	

	/**
	 * Vendor table status count.
	 * 
	 * Gets the number of vendors with
	 * a particular status.
	 *
	 * @since 2.3
	 * @access public
	 *
	 * @param  array $statuses Statuses to include in count.
	 * @return int The number of matching vendors who
	 *             match a particular status.
	 */
	function get_count( $statuses ) {
		$vendors = new FES_DB_Vendors();

		$total = 0;
		foreach ( $statuses as $status ) {
			$total = $total + $vendors->count( array( 'status' => $status ) );
		}

		return $total;
	}

	/**
	 * Vendor table sortable columns.
	 * 
	 * Inclusion of a column in the returned array
	 * means that column is sortable.
	 *
	 * @since 2.3
	 * @access public
	 *
	 * @return array The sortable columns
	 */
	function get_sortable_columns() {
		$sortable_columns = array(
			'name'          => array( 'name',           true  ),     // true means it's already sorted
			'status'        => array( 'status',         false ),
			'products'      => array( 'product_count',  false ),
			'sales_count'   => array( 'sales_count',    false ),
			'sales_value'   => array( 'sales_value',    false ),
			'date_created'  => array( 'date_created',   false ),
		);
		return $sortable_columns;
	}

	/**
	 * Vendor table constuctor.
	 *
	 * This functions essentially serves
	 * as a constructor for the vendor
	 * table.
	 *
	 * @since 2.3
	 * @access public
	 * 
	 * @uses $this->_column_headers
	 * @uses $this->items
	 * @uses $this->get_columns()
	 * @uses $this->get_sortable_columns()
	 * @uses $this->get_pagenum()
	 * @uses $this->set_pagination_args()
	 *
	 * @return void
	 */
	function prepare_items() {

		$columns = $this->get_columns();
		$hidden = array(); // no hidden columns

		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$current_page = $this->get_pagenum();

		$this->items = $this->get_vendors();
		$this->total = $this->pagination_count();
		$this->set_pagination_args( array(
			'total_items' => $this->total,
			'per_page'    => $this->per_page,
			'total_pages' => ceil( $this->total / $this->per_page )
		) );
	}
}
