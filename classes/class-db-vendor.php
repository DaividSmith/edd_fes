<?php
/**
 * FES Database Vendors
 *
 * The extension of EDD_DB to store vendors
 * in a custom database table.
 *
 * @package FES
 * @subpackage Vendors
 * @since 2.3.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { 
	exit;
}

/**
 * FES Database Vendors.
 *
 * The database layer for FES Vendors.
 *
 * @since 2.3.0
 * @access public
 */
class FES_DB_Vendors extends EDD_DB  {

	/**
	 * FES DB Vendors setup.
	 *
	 * Sets up the object properties.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @global $wpdb WordPress database object.
	 * 
	 * @return void
	 */	
	public function __construct() {

		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'fes_vendors';
		$this->primary_key = 'id';
		$this->version     = '1.0';
		
		add_action( 'profile_update', array( $this, 'update_vendor_email_on_user_update' ), 10, 2 );
	}

	/**
	 * Get vendor table columns.
	 *
	 * Get columns and formats of the
	 * vendor table.
	 *
	 * @since 2.3.0
	 * @access public
	 * 
	 * @return array Columns in table.
	 */	
	public function get_columns() {
		return array(
			'id'             => '%d',
			'user_id'        => '%d',
			'username'       => '%s',
			'name'           => '%s',
			'email'          => '%s',
			'product_count'  => '%d',
			'sales_count'	 => '%d',
			'sales_value'	 => '%f',
			'status'		 => '%s',
			'notes'          => '%s',
			'date_created'   => '%s',
		);
	}

	/**
	 * Get default vendor table values.
	 *
	 * Get the default values of columns in the
	 * vendor table.
	 *
	 * @since 2.3.0
	 * @access public
	 * 
	 * @return array Default values of columns in table.
	 */
	public function get_column_defaults() {
		return array(
			'user_id'        => 0,
			'email'          => '',
			'username'       => '',
			'name'           => '',
			'product_count'  => 0,
			'sales_count'	 => 0,
			'sales_value'	 => 0.00,
			'status'		 => 'pending',
			'notes'          => '',
			'date_created'   => date( 'Y-m-d H:i:s' ),
		);
	}

	/**
	 * Get default vendor table values.
	 *
	 * Get the default values of columns in the
	 * vendor table.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @param  array $args Vendor properries.
	 * @return int Vendor ID of inserted vendor.
	 */
	public function add( $args = array() ) {

		if ( empty( $args['email'] ) ) {
			return false;
		}

		$vendor = $this->get_vendor_by( 'email', $args['email'] );
		
		if ( $vendor ) {
			// update an existing vendor
			$this->update( $vendor->id, $args );
			return $vendor->id;
		} else {
			return $this->insert( $args, 'vendor' );
		}
	}

	/**
	 * See if vendor exists.
	 *
	 * Checks if a vendor exists by any custom field
	 * or field/value pair.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @param  string $field Column of vendor table.
	 * @param  string $value Optional. Value of field. 
	 * @return bool If vendor exists.
	 */
	public function exists( $field = 'email', $value = 0 ) {
		return (bool) $this->get_column_by( 'id', $field, $value );
	}

	/**
	 * Increments vendor sales stats.
	 *
	 * Increases a vendor's sales count and value.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @param  int $vendor_id Vendor ID.
	 * @param  string $amount Amount to increase sales value by. 
	 * @return bool If succesfully incremented.
	 */
	public function increment_stats( $vendor_id = 0, $amount = 0.00 ) {

		$vendor = new FES_Vendor( $vendor_id );

		if ( empty( $vendor->id ) ) {
			return false;
		}

		$increased_count = $vendor->increase_sales_count();
		$increased_value = $vendor->increase_sales_value( $amount );

		return ( $increased_count && $increased_value ) ? true : false;

	}

	/**
	 * Decrement vendor sales stats.
	 *
	 * Decreases a vendor's sales count and value.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @param  int $vendor_id Vendor ID.
	 * @param  string $amount Amount to decrease sales value by. 
	 * @return bool If succesfully decremented.
	 */
	public function decrement_stats( $vendor_id = 0, $amount = 0.00 ) {

		$vendor = new FES_Vendor( $vendor_id );

		if ( ! $vendor ) {
			return false;
		}

		$decreased_count = $vendor->decrease_sales_count();
		$decreased_value = $vendor->decrease_sales_value( $amount );

		return ( $decreased_count && $decreased_value ) ? true : false;
	}

	/**
	 * Retrieves a single vendor from the database.
	 *
	 * Gets a vendor from the database table by user id,
	 * vendor id, or email.
	 *
	 * @since  2.3.0
	 * @access public
	 *
	 * @global $wpdb WordPress database object.
	 * 
	 * @param  string $column id or email or login.
	 * @param  mixed  $value  The Vendor ID or email to search.
	 * @return mixed          Upon success, an object of the vendor. Upon failure, NULL.
	 */
	public function get_vendor_by( $field = 'id', $value = 0 ) {
		global $wpdb;

		if ( empty( $field ) || empty( $value ) ) {
			return NULL;
		}

		if ( 'id' == $field || 'user_id' == $field ) {
			// Make sure the value is numeric to avoid casting objects, for example,
			// to int 1.
			if ( ! is_numeric( $value ) ) {
				return false;
			}

			$value = intval( $value );

			if ( $value < 1 ) {
				return false;
			}

		} elseif ( 'email' === $field ) {

			if ( ! is_email( $value ) ) {
				return false;
			}

			$value = trim( $value );
		} elseif ( 'login' === $field ) {

			$value = trim( $value );
		}

		if ( ! $value ) {
			return false;
		}

		switch ( $field ) {
			case 'id':
				$db_field = 'id';
				break;
			case 'email':
				$value    = sanitize_text_field( $value );
				$db_field = 'email';
				break;
			case 'login':
				$value    = sanitize_text_field( $value );
				$db_field = 'username';
				break;
			case 'user_id':
				$db_field = 'user_id';
				break;
			default:
				return false;
		}

		if ( ! $vendor = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $this->table_name WHERE $db_field = %s LIMIT 1", $value ) ) ) {
			return false;
		}

		return $vendor;
	}

	/**
	 * Retrieve vendors from the database
	 *
	 * Get vendors by user id, vendor id, email, status,
	 * date range, or username.
	 *
	 * @access  public
	 * @since   2.3.0
	 *
	 * @global $wpdb WordPress database object.
	 * 
	 * @param  array $args Arguments for search.
	 * @return array Vendors found.
	*/
	public function get_vendors( $args = array() ) {

		global $wpdb;

		$defaults = array(
			'number'       => 20,
			'offset'       => 0,
			'user_id'      => 0,
			'orderby'      => 'id',
			'order'        => 'DESC'
		);

		$args  = wp_parse_args( $args, $defaults );

		if ( $args['number'] < 1 ) {
			$args['number'] = 999999999999;
		}

		$where = '';

		// specific vendors
		if ( ! empty( $args['id'] ) ) {

			if ( is_array( $args['id'] ) ) {
				$ids = implode( ',', $args['id'] );
			} else {
				$ids = intval( $args['id'] );
			}

			$where .= " WHERE `id` IN( {$ids} ) ";

		}

		// vendors for specific user accounts
		if ( ! empty( $args['user_id'] ) ) {

			if ( is_array( $args['user_id'] ) ) {
				$user_ids = implode( ',', $args['user_id'] );
			} else {
				$user_ids = intval( $args['user_id'] );
			}

			if ( ! empty( $where ) ) {
				$where .= " AND `user_id` IN( {$user_ids} ) ";
			} else {
				$where .= " WHERE `user_id` IN( {$user_ids} ) ";
			}


		}

		//specific vendors by email
		if ( ! empty( $args['email'] ) ) {

			if ( is_array( $args['email'] ) ) {
				$emails = "'" . implode( "', '", $args['email'] ) . "'";
			} else {
				$emails = "'" . $args['email'] . "'";
			}

			if ( ! empty( $where ) ) {
				$where .= " AND `email` IN( {$emails} ) ";
			} else {
				$where .= " WHERE `email` IN( {$emails} ) ";
			}

		}

		// specific vendors by username
		if ( ! empty( $args['username'] ) ) {

			if ( ! empty( $where ) ) {
				$where .= " AND `username` LIKE '" . $args['username'] . "' ";
			} else {
				$where .= " WHERE `username` LIKE '%%" . $args['username'] . "%%' ";
			}
		}

		// specific vendors by name
		if ( ! empty( $args['name'] ) ) {

			if ( ! empty( $where ) ) {
				$where .= " AND `name` LIKE '" . $args['name'] . "' ";
			} else {
				$where .= " WHERE `name` LIKE '%%" . $args['name'] . "%%' ";
			}
		}

		// specific vendors by status
		if ( ! empty( $args['status'] ) ) {

			if ( ! empty( $where ) ) {
				$where .= " AND `status` LIKE '" . $args['status'] . "' ";
			} else {
				$where .= " WHERE `status` LIKE '%%" . $args['status'] . "%%' ";
			}
		}

		// Vendors created for a specific date or in a date range
		if ( ! empty( $args['date'] ) ) {

			if ( is_array( $args['date'] ) ) {

				if ( ! empty( $args['date']['start'] ) ) {

					$start = date( 'Y-m-d H:i:s', strtotime( $args['date']['start'] ) );

					if ( ! empty( $where ) ) {

						$where .= " AND `date_created` >= '{$start}'";

					} else {

						$where .= " WHERE `date_created` >= '{$start}'";

					}

				}

				if ( ! empty( $args['date']['end'] ) ) {

					$end = date( 'Y-m-d H:i:s', strtotime( $args['date']['end'] ) );

					if ( ! empty( $where ) ) {

						$where .= " AND `date_created` <= '{$end}'";

					} else {

						$where .= " WHERE `date_created` <= '{$end}'";

					}

				}

			} else {

				$year  = date( 'Y', strtotime( $args['date'] ) );
				$month = date( 'm', strtotime( $args['date'] ) );
				$day   = date( 'd', strtotime( $args['date'] ) );

				if ( empty( $where ) ) {
					$where .= " WHERE";
				} else {
					$where .= " AND";
				}

				$where .= " $year = YEAR ( date_created ) AND $month = MONTH ( date_created ) AND $day = DAY ( date_created )";
			}

		}

		$args['orderby'] = ! array_key_exists( $args['orderby'], $this->get_columns() ) ? 'id' : $args['orderby'];

		if ( 'sales_value' == $args['orderby'] ) {
			$args['orderby'] = 'sales_value+0';
		}

		$cache_key = md5( 'edd_vendors_' . serialize( $args ) );

		$vendors = wp_cache_get( $cache_key, 'vendors' );

		if ( $vendors === false ) {
			$vendors = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  $this->table_name $where ORDER BY {$args['orderby']} {$args['order']} LIMIT %d,%d;", absint( $args['offset'] ), absint( $args['number'] ) ) );
			wp_cache_set( $cache_key, $vendors, 'vendors', 3600 );
		}

		return $vendors;

	}

	/**
	 * Count vendors in the database
	 *
	 * Count vendors by user id, vendor id, email, status,
	 * date range, or username.
	 *
	 * @access  public
	 * @since   2.3.0
	 *
	 * @global $wpdb WordPress database object.
	 * 
	 * @param  array $args Arguments for search.
	 * @return int Count of vendors found.
	*/
	public function count( $args = array() ) {

		global $wpdb;

		$defaults = array(
			'number'       => 20,
			'offset'       => 0,
			'user_id'      => 0,
			'orderby'      => 'id',
			'order'        => 'DESC'
		);

		$args  = wp_parse_args( $args, $defaults );

		if ( $args['number'] < 1 ) {
			$args['number'] = 999999999999;
		}

		$where = '';

		// specific vendors
		if ( ! empty( $args['id'] ) ) {

			if ( is_array( $args['id'] ) ) {
				$ids = implode( ',', $args['id'] );
			} else {
				$ids = intval( $args['id'] );
			}

			$where .= " WHERE `id` IN( {$ids} ) ";

		}

		// vendors for specific user accounts
		if ( ! empty( $args['user_id'] ) ) {

			if ( is_array( $args['user_id'] ) ) {
				$user_ids = implode( ',', $args['user_id'] );
			} else {
				$user_ids = intval( $args['user_id'] );
			}

			if ( ! empty( $where ) ) {
				$where .= " AND `user_id` IN( {$user_ids} ) ";
			} else {
				$where .= " WHERE `user_id` IN( {$user_ids} ) ";
			}


		}

		//specific vendors by email
		if ( ! empty( $args['email'] ) ) {

			if ( is_array( $args['email'] ) ) {
				$emails = "'" . implode( "', '", $args['email'] ) . "'";
			} else {
				$emails = "'" . $args['email'] . "'";
			}

			if ( ! empty( $where ) ) {
				$where .= " AND `email` IN( {$emails} ) ";
			} else {
				$where .= " WHERE `email` IN( {$emails} ) ";
			}

		}

		// specific vendors by username
		if ( ! empty( $args['username'] ) ) {

			if ( ! empty( $where ) ) {
				$where .= " AND `username` LIKE '" . $args['username'] . "' ";
			} else {
				$where .= " WHERE `username` LIKE '%%" . $args['username'] . "%%' ";
			}
		}

		// specific vendors by name
		if ( ! empty( $args['name'] ) ) {

			if ( ! empty( $where ) ) {
				$where .= " AND `name` LIKE '" . $args['name'] . "' ";
			} else {
				$where .= " WHERE `name` LIKE '%%" . $args['name'] . "%%' ";
			}
		}

		// specific vendors by status
		if ( ! empty( $args['status'] ) ) {

			if ( ! empty( $where ) ) {
				$where .= " AND `status` LIKE '" . $args['status'] . "' ";
			} else {
				$where .= " WHERE `status` LIKE '%%" . $args['status'] . "%%' ";
			}
		}

		// Vendors created for a specific date or in a date range
		if ( ! empty( $args['date'] ) ) {

			if ( is_array( $args['date'] ) ) {

				if ( ! empty( $args['date']['start'] ) ) {

					$start = date( 'Y-m-d H:i:s', strtotime( $args['date']['start'] ) );

					if ( ! empty( $where ) ) {

						$where .= " AND `date_created` >= '{$start}'";

					} else {

						$where .= " WHERE `date_created` >= '{$start}'";

					}

				}

				if ( ! empty( $args['date']['end'] ) ) {

					$end = date( 'Y-m-d H:i:s', strtotime( $args['date']['end'] ) );

					if ( ! empty( $where ) ) {

						$where .= " AND `date_created` <= '{$end}'";

					} else {

						$where .= " WHERE `date_created` <= '{$end}'";

					}

				}

			} else {

				$year  = date( 'Y', strtotime( $args['date'] ) );
				$month = date( 'm', strtotime( $args['date'] ) );
				$day   = date( 'd', strtotime( $args['date'] ) );

				if ( empty( $where ) ) {
					$where .= " WHERE";
				} else {
					$where .= " AND";
				}

				$where .= " $year = YEAR ( date_created ) AND $month = MONTH ( date_created ) AND $day = DAY ( date_created )";
			}

		}

		$args['orderby'] = ! array_key_exists( $args['orderby'], $this->get_columns() ) ? 'id' : $args['orderby'];

		if ( 'sales_value' == $args['orderby'] ) {
			$args['orderby'] = 'sales_value+0';
		}

		$cache_key = md5( 'fes_vendors_count' . serialize( $args ) );

		$count = wp_cache_get( $cache_key, 'vendors' );

		if ( $count === false ) {
			$count = $wpdb->get_var( "SELECT COUNT($this->primary_key) FROM " . $this->table_name . "{$where};" );
			wp_cache_set( $cache_key, $count, 'vendors', 3600 );
		}

		return absint( $count );

	}

	/**
	 * Sync vendor/user emails
	 *
	 * Updates the email address of a vendor record
	 * when the email on a user is updated
	 *
	 * @access  public
	 * @since   2.3.0
	 * 
	 * @param  array $user_id User id of user being updated.
	 * @param  array $old_user_data Old user data. Unused.
	 * @return void
	*/
	public function update_vendor_email_on_user_update( $user_id = 0, $old_user_data ) {
		$vendor = new FES_Vendor( $user_id, true );
		if ( ! $vendor ) {
			return false;
		}
		$user = get_userdata( $user_id );
		if ( ! empty( $user ) && $user->user_email !== $vendor->email ) {
			if ( ! $this->get_vendor_by( 'email', $user->user_email ) ) {
				$success = $this->update( $vendor->id, array( 'email' => $user->user_email ) );
				if ( $success ) {
					/**
					 * Update Vendor Email on User Update
					 *
					 * An action that runs when a vendor email is updated 
					 * because a user email was changed.
					 *
					 * @since 2.0.0
					 *
					 * @param  WP_User $user User object of edited user.
					 * @param  FES_Vendor $vendor Vendor object of edited user.
					 */	
					do_action( 'fes_update_vendor_email_on_user_update', $user, $vendor );
				}
			}
		}
	}

	/**
	 * Create the table.
	 *
	 * Creates vendor database table.
	 *
	 * @access  public
	 * @since   2.3.0
	*/
	public function create_table() {

		global $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		if ( $wpdb->get_var( "SHOW TABLES LIKE '$this->table_name'" ) == $this->table_name ) {
			return;
		}

		$sql = "CREATE TABLE " . $this->table_name . " (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		user_id bigint(20) NOT NULL,
		username varchar(50) NOT NULL,
		email varchar(50) NOT NULL,
		name mediumtext NOT NULL,
		product_count bigint(20) NOT NULL,
		sales_value mediumtext NOT NULL,
		sales_count bigint(20) NOT NULL,
		status mediumtext NOT NULL,
		notes longtext NOT NULL,
		date_created datetime NOT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY email (email),
		KEY user (user_id)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );
	}
}
