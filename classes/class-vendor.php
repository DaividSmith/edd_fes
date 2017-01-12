<?php
/**
 * FES Vendor
 *
 * This file creates FES Vendor objects, not 
 * to be confused with class-vendors.php which
 * is vendor functions.
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
 * FES Vendor class.
 *
 * Creates FES Vendor objects that are
 * similar in nature to EDD Customer objects.
 *
 * @since 2.3.0
 * @access public
 */
class FES_Vendor {

	/**
	 * The vendor ID
	 *
	 * @since 2.3.0
	 * @access public
	 * @var int $id Vendor ID not User ID.
	 */	
	public $id = 0;

	/**
	 * The vendor's product count
	 *
	 * @since 2.3.0
	 * @access public
	 * @var int $product_count Number of products
	 *      				   a vendor has.
	 */		
	public $product_count = 0;


	/**
	 * The vendor's sales count
	 *
	 * @since 2.3.0
	 * @access public
	 * @var int $sales_count Number of sales
	 *      				   a vendor has.
	 */		
	public $sales_count = 0;

	/**
	 * The vendor's sales value
	 *
	 * @since 2.3.0
	 * @access public
	 * @var double $sales_value Amount of sales
	 *      				 revenue generated.
	 */	
	public $sales_value = 0;

	/**
	 * The vendor's status
	 *
	 * @since 2.3.0
	 * @access public
	 * @var string $status Vendor status.
	 */	
	public $status = 'pending';

	/**
	 * Whether vendor exists
	 *
	 * @since 2.3.0
	 * @access public
	 * @var bool $valid Used in vendor 
	 *      			exists checks.
	 */	
	public $valid = false;

	/**
	 * The vendor's email
	 *
	 * @since 2.3.0
	 * @access public
	 * @var string $email Vendor email.
	 */
	public $email;

	/**
	 * The vendor's name
	 *
	 * @since 2.3.0
	 * @access public
	 * @var string $name Vendor name.
	 */
	public $name;

	/**
	 * The vendor's username
	 *
	 * @since 2.3.0
	 * @access public
	 * @var string $username Vendor username.
	 */
	public $username;


	/**
	 * The vendor's registration date
	 *
	 * @since 2.3.0
	 * @access public
	 * @var string $date_created Vendor registration date.
	 */
	public $date_created;

	/**
	 * The vendor's user ID
	 *
	 * @since 2.3.0
	 * @access public
	 * @var int $user_id Vendor user ID.
	 */
	public $user_id;


	/**
	 * The vendor's notes
	 *
	 * @since 2.3.0
	 * @access public
	 * @var array $notes Vendor notes.
	 */
	public $notes;

	/**
	 * The Database Abstraction
	 *
	 * @since 2.3.0
	 * @access protected
	 * @var FES_Vendor_DB $db Vendor db object.
	 */	
	protected $db;

	/**
	 * FES Vendor setup.
	 *
	 * Creates a vendor object based on user id, vendor id
	 * or vendor email.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @param int|string $_id_or_email Username, email, user id or vendor id.
	 * @param bool $by_user_id If id passed in, is it a user id.
	 * @return void
	 */		
	public function __construct( $_id_or_email = false, $by_user_id = false ) {

		if ( false == $_id_or_email || ( is_numeric( $_id_or_email ) && (int) $_id_or_email !== absint( $_id_or_email ) ) ) {
			return false;
		}

		$this->db = new FES_DB_Vendors;

		$by_user_id = is_bool( $by_user_id ) ? $by_user_id : false;

		if ( is_numeric( $_id_or_email ) ) {
			$field = $by_user_id ? 'user_id' : 'id';
		} else if ( is_email( $_id_or_email ) ) {
			$field = 'email';
		} else {
			$field = 'username';
		}

		$vendor = $this->db->get_vendor_by( $field, $_id_or_email );

		if ( empty( $vendor ) || ! is_object( $vendor ) ) {
			return false;
		} else {
			$this->valid = true;
		}

		$this->setup_vendor( $vendor );

	}

	/**
	 * Setup Vendor.
	 * 
	 * Given the vendor data, let's set the variables.
	 *
	 * @since  2.3.0
	 * @access private
	 * 
	 * @param  FES_Vendor $vendor The Vendor Object.
	 * @return bool If the setup was successful or not.
	 */
	private function setup_vendor( $vendor ) {

		if ( ! is_object( $vendor ) ) {
			return false;
		}

		foreach ( $vendor as $key => $value ) {

			switch ( $key ) {

				case 'notes':
					$this->$key = $this->get_notes();
					break;

				default:
					$this->$key = $value;
					break;

			}

		}

		// Vendor ID and email are the only things that are necessary, make sure they exist
		if ( ! empty( $this->id ) && ! empty( $this->email ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Vendor Object Get Method.
	 * 
	 * Magic __get function to dispatch a call to retrieve a private property
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @param string $key Key to get.
	 * @param mixed Variable retrieved.
	 */
	public function __get( $key ) {
		if ( method_exists( $this, 'get_' . $key ) ) {
			return call_user_func( array( $this, 'get_' . $key ) );
		} else {
			return new WP_Error( 'fes-vendor-invalid-property', sprintf( __( 'Can\'t get property %s', 'edd_fes' ), $key ) );
		}
	}

	/**
	 * Create Vendor.
	 *
	 * Based on arguments passed in, creates an FES
	 * Vendor by adding a row in the vendor custom table
	 * in the database.
	 *
	 * @since  2.3.0
	 * @access public
	 * 
	 * @param  array  $data Array of attributes for a vendor.
	 * @return mixed  False if not a valid creation, Vendor ID if user is found or valid creation.
	 */
	public function create( $args = array() ) {

		if ( $this->id != 0 || empty( $args ) ) {
			return false;
		}

		$args = $this->sanitize_columns( $args );

		if ( empty( $args['email'] ) || ! is_email( $args['email'] ) ) {
			return false;
		}
		/**
		 * FES Before Vendor Create
		 *
		 * Run action before a vendor is created.
		 *
		 * @since 2.3.0
		 * @param array $args Array of vendor data.
		 */
		do_action( 'fes_vendor_pre_create', $args );

		$created = false;

		$data = $args;

		// The DB class 'add' implies an update if the vendor being asked to be created already exists
		if ( $this->db->add( $data ) ) {

			// We've successfully added/updated the vendor, reset the class vars with the new data
			$vendor = $this->db->get_vendor_by( 'email', $args['email'] );

			// Setup the vendor data with the values from DB
			$this->setup_vendor( $vendor );

			$created = $this->id;
		}

		/**
		 * FES After Vendor Create
		 *
		 * Run action after a vendor is created.
		 *
		 * @since 2.3.0
		 * @param bool $created Whether or not a vendor was created.
		 * @param array $args Array of vendor data.
		 */
		do_action( 'fes_vendor_post_create', $created, $args );

		return $created;

	}

	/**
	 * Update a vendor record.
	 *
	 * Given an array of data to update, update a vendor
	 * in the FES vendor custom table.
	 *
	 * @since  2.3.0
	 * @access public
	 * 
	 * @param  array  $data Array of data attributes for a vendor (checked via whitelist)
	 * @return bool         If the update was successful or not
	 */
	public function update( $data = array() ) {

		if ( empty( $data ) ) {
			return false;
		}

		if ( ! is_object( $this->db ) ){
			$this->db = new FES_DB_Vendors;
		}

		$data = $this->sanitize_columns( $data );
		/**
		 * FES Before Vendor Updated
		 *
		 * Run action before a vendor is updated.
		 *
		 * @since 2.3.0
		 * 
		 * @param int $id Vendor ID of vendor updated.
		 * @param array $data Array of vendor data to update.
		 */
		do_action( 'fes_vendor_before_update', $this->id, $data );

		$updated = false;

		if ( $this->db->update( $this->id, $data ) ) {

			$vendor = $this->db->get_vendor_by( 'id', $this->id );
			$this->setup_vendor( $vendor);

			$updated = true;
		}
		/**
		 * FES After Vendor Updated
		 *
		 * Run action after a vendor is updated.
		 *
		 * @since 2.3.0
		 * 
		 * @param bool $updated Whether or not a vendor was updated.
		 * @param int $id Vendor ID of vendor updated.
		 * @param array $data Array of vendor data to update.
		 */
		do_action( 'fes_vendor_after_update', $updated, $this->id, $data );

		return $updated;
	}

	/**
	 * Increase the product count of a vendor.
	 *
	 * Allows for the product count of a vendor to be
	 * increased (if for example product is approved).
	 *
	 * @since  2.3.0
	 * @access public
	 * 
	 * @param  int $count The number to imcrement by.
	 * @return int The product count.
	 */
	public function increase_product_count( $count = 1 ) {

		// Make sure it's numeric and not negative
		if ( ! is_numeric( $count ) || $count != absint( $count ) ) {
			return false;
		}

		if ( ! is_object( $this->db ) ){
			$this->db = new FES_DB_Vendors;
		}		

		$new_total = (int) $this->product_count + (int) $count;
		/**
		 * FES Before Vendor Increase Product Count
		 *
		 * Run action before a vendor product count is
		 * increased.
		 *
		 * @since 2.3.0
		 * 
		 * @param int $count Number to increase count by.
		 * @param int $id Vendor ID of vendor updated.
		 */
		do_action( 'fes_vendor_pre_increase_product_count', $count, $this->id );

		if ( $this->update( array( 'product_count' => $new_total ) ) ) {
			$this->sales_count = $new_total;
		}
		/**
		 * FES After Vendor Increase Product Count
		 *
		 * Run action after a vendor product count is
		 * increased.
		 *
		 * @since 2.3.0
		 *
		 * @param int $product_count Vendor product count after update.
		 * @param int $count Number to increase count by.
		 * @param int $id Vendor ID of vendor updated.
		 */
		do_action( 'fes_vendor_post_increase_product_count', $this->product_count, $count, $this->id );

		return $this->product_count;
	}

	/**
	 * Decrease the vendor product count.
	 *
	 * Allows for the product count of a vendor to be
	 * decreased (if for example product is revoked).
	 *
	 * @since  2.3.0
	 * @access public
	 * 
	 * @param  int $count The amount to decrease by.
	 * @return mixed If successful, the new count, otherwise false.
	 */
	public function decrease_product_count( $count = 1 ) {

		// Make sure it's numeric and not negative
		if ( ! is_numeric( $count ) || $count != absint( $count ) ) {
			return false;
		}

		if ( ! is_object( $this->db ) ){
			$this->db = new FES_DB_Vendors;
		}

		$new_total = (int) $this->product_count - (int) $count;

		if ( $new_total < 0 ) {
			$new_total = 0;
		}
		/**
		 * FES Before Vendor Decrease Product Count
		 *
		 * Run action before a vendor product count is
		 * decreased.
		 *
		 * @since 2.3.0
		 * 
		 * @param int $count Number to decrease count by.
		 * @param int $id Vendor ID of vendor updated.
		 */
		do_action( 'fes_vendor_pre_decrease_product_count', $count, $this->id );

		if ( $this->update( array( 'product_count' => $new_total ) ) ) {
			$this->sales_count = $new_total;
		}
		/**
		 * FES After Vendor Decrease Product Count
		 *
		 * Run action after a vendor product count is
		 * decreased.
		 *
		 * @since 2.3.0
		 *
		 * @param int $product_count Vendor product count after update.
		 * @param int $count Number to decrease count by.
		 * @param int $id Vendor ID of vendor updated.
		 */
		do_action( 'fes_vendor_post_decrease_product_count', $this->product_count, $count, $this->id );

		return $this->product_count;
	}

	/**
	 * Increase the sales count of a vendor.
	 *
	 * Increases the sales count of a vendor
	 * by the amount passed in (if for example a new
	 * order is placed).
	 *
	 * @since  2.3.0
	 * @access public
	 * 
	 * @param  int $count The number to increment by.
	 * @return int The sales count.
	 */
	public function increase_sales_count( $count = 1 ) {

		// Make sure it's numeric and not negative
		if ( ! is_numeric( $count ) || $count != absint( $count ) ) {
			return false;
		}

		if ( ! is_object( $this->db ) ){
			$this->db = new FES_DB_Vendors;
		}		

		$new_total = (int) $this->sales_count + (int) $count;
		/**
		 * FES Before Vendor Increase Sales Count
		 *
		 * Run action before a vendor sales count is
		 * increased.
		 *
		 * @since 2.3.0
		 * 
		 * @param int $count Number to increase count by.
		 * @param int $id Vendor ID of vendor updated.
		 */
		do_action( 'fes_vendor_pre_increase_sales_count', $count, $this->id );

		if ( $this->update( array( 'sales_count' => $new_total ) ) ) {
			$this->sales_count = $new_total;
		}
		/**
		 * FES After Vendor Increase Sales Count
		 *
		 * Run action after a vendor sales count is
		 * increased.
		 *
		 * @since 2.3.0
		 *
		 * @param int $sales_count Vendor sales count after update.
		 * @param int $count Number to increase count by.
		 * @param int $id Vendor ID of vendor updated.
		 */
		do_action( 'fes_vendor_post_increase_sales_count', $this->sales_count, $count, $this->id );

		return $this->sales_count;
	}

	/**
	 * Decrease the sales count of a vendor.
	 *
	 * Decreases the sales count of a vendor
	 * by the amount passed in (if for example a order is
	 * revoked or refunded).
	 *
	 * @since  2.3.0
	 * @access public
	 * 
	 * @param  int $count The number to decrease by.
	 * @return int The sales count.
	 */
	public function decrease_sales_count( $count = 1 ) {

		// Make sure it's numeric and not negative
		if ( ! is_numeric( $count ) || $count != absint( $count ) ) {
			return false;
		}

		if ( ! is_object( $this->db ) ){
			$this->db = new FES_DB_Vendors;
		}		

		$new_total = (int) $this->sales_count - (int) $count;

		if ( $new_total < 0 ) {
			$new_total = 0;
		}
		/**
		 * FES Before Vendor Decrease Sales Count
		 *
		 * Run action before a vendor sales count is
		 * decreased.
		 *
		 * @since 2.3.0
		 * 
		 * @param int $count Number to decrease count by.
		 * @param int $id Vendor ID of vendor updated.
		 */
		do_action( 'fes_vendor_pre_decrease_sales_count', $count, $this->id );

		if ( $this->update( array( 'sales_count' => $new_total ) ) ) {
			$this->sales_count = $new_total;
		}
		/**
		 * FES After Vendor Decrease Sales Count
		 *
		 * Run action after a vendor sales count is
		 * decreased.
		 *
		 * @since 2.3.0
		 *
		 * @param int $sales_count Vendor sales count after update.
		 * @param int $count Number to decrease count by.
		 * @param int $id Vendor ID of vendor updated.
		 */
		do_action( 'fes_vendor_post_decrease_sales_count', $this->sales_count, $count, $this->id );

		return $this->sales_count;
	}

	/**
	 * Increase the vendor's sales value.
	 *
	 * For example if new order is placed, then the vendor's sales value
	 * should be incremented to match. This is the function that's called
	 * to do that.
	 * 
	 * @since  2.3
	 * @access public
	 * 
	 * @param  float  $value The value to increase by
	 * @return mixed         If successful, the new value, otherwise false
	 */
	public function increase_value( $value = 0.00 ) {

		if ( ! is_object( $this->db ) ){
			$this->db = new FES_DB_Vendors;
		}

		$new_value = floatval( $this->sales_value ) + $value;
		/**
		 * FES Before Vendor Increase Sales Value
		 *
		 * Run action before a vendor sales value is
		 * increased.
		 *
		 * @since 2.3.0
		 *
		 * @param float $value Number to increase value by.
		 * @param int $id Vendor ID of vendor updated.
		 */
		do_action( 'fes_vendor_pre_increase_value', $value, $this->id );

		if ( $this->update( array( 'sales_value' => $new_value ) ) ) {
			$this->sales_value = $new_value;
		}
		/**
		 * FES After Vendor Increase Sales Value
		 *
		 * Run action after a vendor sales value is
		 * increased.
		 *
		 * @since 2.3.0
		 *
		 * @param float $sales_value Vendor sales value after update.
		 * @param float $value Number to increase value by.
		 * @param int $id Vendor ID of vendor updated.
		 */
		do_action( 'fes_vendor_post_increase_value', $this->sales_value, $value, $this->id );

		return $this->sales_value;
	}

	/**
	 * Decrease a vendor's sales value.
	 *
	 * For example if an order is revoked, then the vendor's sales value
	 * should be decremented to match. This is the function that's called
	 * to do that.
	 *
	 * @since  2.3
	 * @access public
	 * 
	 * @param  float  $value The value to decrease by
	 * @return mixed         If successful, the new value, otherwise false
	 */
	public function decrease_value( $value = 0.00 ) {

		if ( ! is_object( $this->db ) ){
			$this->db = new FES_DB_Vendors;
		}

		$new_value = floatval( $this->sales_value ) - $value;

		if ( $new_value < 0 ) {
			$new_value = 0.00;
		}
		/**
		 * FES Before Vendor Decrease Sales Value
		 *
		 * Run action before a vendor sales value is
		 * decreased.
		 *
		 * @since 2.3.0
		 *
		 * @param float $value Number to decrease value by.
		 * @param int $id Vendor ID of vendor updated.
		 */
		do_action( 'fes_vendor_pre_decrease_value', $value, $this->id );

		if ( $this->update( array( 'sales_value' => $new_value ) ) ) {
			$this->sales_value = $new_value;
		}
		/**
		 * FES After Vendor Decrease Sales Value
		 *
		 * Run action after a vendor sales value is
		 * decreased.
		 *
		 * @since 2.3.0
		 *
		 * @param float $sales_value Vendor sales value after update.
		 * @param float $value Number to decrease value by.
		 * @param int $id Vendor ID of vendor updated.
		 */
		do_action( 'fes_vendor_post_decrease_value', $this->sales_value, $value, $this->id );

		return $this->sales_value;
	}

	/**
	 * Get the parsed notes for a vendor as an array.
	 *
	 * Retrieves the notes for a vendor from the database,
	 * and then organizes them into a nice array.
	 * 
	 * @since  2.3.0
	 * @access public
	 * 
	 * @param  int $length The number of notes to get.
	 * @param  int $paged What note to start at.
	 * @return array           The notes requsted.
	 */
	public function get_notes( $length = 20, $paged = 1 ) {

		$length = is_numeric( $length ) ? $length : 20;
		$offset = is_numeric( $paged ) && $paged != 1 ? ( ( absint( $paged ) - 1 ) * $length ) : 0;

		$all_notes   = $this->get_raw_notes();
		$notes_array = array_reverse( array_filter( explode( "\n\n", $all_notes ) ) );

		$desired_notes = array_slice( $notes_array, $offset, $length );

		return $desired_notes;

	}

	/**
	 * Number of vendor notes.
	 * 
	 * Get the total number of notes we have after parsing.
	 *
	 * @since  2.3.0
	 * @access public
	 * 
	 * @return int The number of notes for the vendor.
	 */
	public function get_notes_count() {

		$all_notes = $this->get_raw_notes();
		$notes_array = array_reverse( array_filter( explode( "\n\n", $all_notes ) ) );

		return count( $notes_array );

	}

	/**
	 * Add a note for the vendor.
	 *
	 * Adds a note for the vendor into the database.
	 *
	 * @since  2.3.0
	 * @access public
	 * 
	 * @param string $note The note to add.
	 * @return string|boolean The new note if added successfully, false otherwise.
	 */
	public function add_note( $note = '' ) {

		$note = trim( $note );
		if ( empty( $note ) ) {
			return false;
		}

		$notes = $this->get_raw_notes();

		if ( empty( $notes ) ) {
			$notes = '';
		}

		$id = get_current_user_id();
		$user = new WP_User( $id );
		$note_string = date_i18n( 'F j, Y H:i:s', current_time( 'timestamp' ) ) . ' '. __( 'by', 'edd_fes' ) . ' ' .  $user->user_login . ': ' . $note;
		/**
		 * Vendor Note String
		 *
		 * Filter which allows for a note's contents to be changed
		 * while it's being inserted.
		 *
		 * @since 2.3.0
		 *
		 * @param string $note_string Note string to add.
		 */
		$new_note    = apply_filters( 'fes_vendor_add_note_string', $note_string );
		$notes      .= "\n\n" . $new_note;
		/**
		 * Before Vendor Note Added
		 *
		 * Action which runs before a new note is added.
		 *
		 * @since 2.3.0
		 *
		 * @param string $new_note Note string which was added.
		 * @param int $id Vendor ID of vendor updated.
		 */
		do_action( 'fes_vendor_pre_add_note', $new_note, $this->id );

		$updated = $this->update( array( 'notes' => $notes ) );

		if ( $updated ) {
			$this->notes = $this->get_notes();
		}
		/**
		 * After Vendor Note Added
		 *
		 * Action which runs after a new note is added.
		 *
		 * @since 2.3.0
		 *
		 * @param array $notes Notes for vendor.
		 * @param string $new_note Note string which was added.
		 * @param int $id Vendor ID of vendor updated.
		 */
		do_action( 'fes_vendor_post_add_note', $this->notes, $new_note, $this->id );

		// Return the formatted note, so we can test, as well as update any displays
		return $new_note;

	}

	/**
	 * Change Vendor Status.
	 *
	 * This function changes a vendor status if allowed,
	 * and also sends out the notifications configured for the 
	 * change status action.
	 *
	 * @since  2.3.0
	 * @access public
	 * 
	 * @param  string $new_status The new vendor status.
	 * @param  bool $in_admin True if in admin, false otherwise.
	 * @param  bool $output Whether to output a return.
	 * @return mixed Void if $output false, else JSON encoded array returned.
	 */
	public function change_status( $new_status = '', $in_admin = false, $output = false ) {

		if ( ! is_object( $this->db ) ){
			$this->db = new FES_DB_Vendors;
		}

		// if there's no status there's nothing we can do
		if ( $new_status === '' ){
			if ( $output ){
				$output = array();
				$output['title'] = __( 'Error!', 'edd_fes' );
				$output['message'] = __( 'No status passed in', 'edd_fes' );
				$output['redirect_to'] = '#';
				$output['success'] = false;
				return $output;
			} else {
				return;
			}
		}

		$old_status = $this->status;

		// if we aren't really changing the status
		if ( $old_status === $new_status ){
			if ( $output ){
				$output = array();
				$output['title'] = __( 'Error!', 'edd_fes' );
				$output['message'] = __( 'New status can\'t be the same as the old status!', 'edd_fes' );
				$output['redirect_to'] = '#';
				$output['success'] = false;
				return $output;
			} else {
				return;
			}
		}

		$arr = array();
		$arr['status'] = $new_status;

		// defaults for emails
		$from_name  = edd_get_option( 'from_name', get_bloginfo( 'name' ) );
		$from_email = edd_get_option( 'from_email', get_bloginfo( 'admin_email' ) );

		if ( $in_admin ){
			// Security breaker: If current user is not admin then get out. 
			if ( ! EDD_FES()->vendors->user_is_admin() ){
				if ( $output ){
					$output = array();
					$output['title'] = __( 'Error!', 'edd_fes' );
					$output['message'] = __( 'You don\'t have permission to do that!', 'edd_fes' );
					$output['redirect_to'] = '#';
					$output['success'] = false;
					return $output;
				} else {
					return;
				}
			}

			if ( $new_status === 'pending' ){
				$user = new WP_User( $this->user_id );
				if ( $this->status !== 'approved' && $this->status !== 'suspended' ) {
					if ( ! ( user_can( $this->user_id, 'subscriber' ) ) ) {
						$user->add_role( 'subscriber' );
					}
				}
				$this->update( $arr );
				// no notifications on admin side for admin inserting a pending vendor
			} else if ( $new_status === 'declined' ){
				// If the user is already a vendor they can't be declined by definition. They need to be rejected. This is a
				// sanity check more than an actual security measure.
				if ( $this->status !== 'pending' ) {
					if ( $output ){
						$output = array();
						$output['title'] = __( 'Error!', 'edd_fes' );
						$output['message'] = __( 'Invalid status change', 'edd_fes' );
						$output['redirect_to'] = '#';
						$output['success'] = false;
						return $output;
					} else {
						return;
					}
				}
				
				// We're good to go, let's remove the pending status
				$user = new WP_User( $this->user_id );

				if ( ! ( user_can( $this->user_id, 'subscriber' ) ) ) {
					$user->add_role( 'subscriber' ); // in case pending_vendor is the only role they have. Puts a hose onto a world that might otherwise be on fire.
				}

				if ( user_can( $this->user_id, 'pending_vendor' ) ) {
					$user->remove_role( 'pending_vendor' );
				}
			   
				/**
				 * Declined Email Subject
				 *
				 * Change the subject of the declined vendor application email.
				 *
				 * @since 2.0.0
				 *
				 * @param string $subject Subject of email.
				 */
				$subject    = apply_filters( 'fes_application_declined_message_subj', __( 'Application Declined', 'edd_fes' ) );
				$message    = EDD_FES()->helper->get_option( 'fes-vendor-app-declined-email', '' );
				EDD_FES()->emails->send_email( $user->user_email, $from_name, $from_email, $subject, $message, "user", $this->user_id, array( 'fes-vendor-app-declined-email-toggle' ) );
				$this->db->delete( $this->id ); // delete vendor row
				
				do_action( 'fes_decline_vendor_admin', $this->user_id );
				if ( $output ){
					$output = array();
					$output['title'] = __( 'Success!', 'edd_fes' );
					$output['message'] = sprintf( _x( '%s application declined successfully', 'FES uppercase singular setting for vendor', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = true ) );
					$output['redirect_to'] = '#';
					$output['success'] = true;
					return $output;
				} else {
					return;
				}
			} else if ( $new_status === 'approved' ){
				// in the admin side, a vendor must have pending status to go to approved (since there's no concept of auto approve vendors in the admin area)
			   if ( $this->status === 'approved' ) {
					if ( $output ){
						$output = array();
						$output['title'] = __( 'Error!', 'edd_fes' );
						$output['message'] = __( 'Invalid status change', 'edd_fes' );
						$output['redirect_to'] = '#';
						$output['success'] = false;
						return $output;
					} else {
						return;
					}
				}
				
				// We're good to go, remove pending status and set to approved
				$user = new WP_User( $this->user_id );

				if ( ! ( user_can( $this->user_id, 'frontend_vendor' ) ) ) {
					$user->add_role( 'frontend_vendor' );
				}

				if ( user_can( $this->user_id, 'pending_vendor' ) ) {
					$user->remove_role( 'pending_vendor' );
				}

				$this->update( $arr );
				/**
				 * Approved Email Subject
				 *
				 * Change the subject of the approved vendor application email.
				 *
				 * @since 2.0.0
				 *
				 * @param string $subject Subject of email.
				 */
				$subject = apply_filters( 'fes_application_approved_message_subj', __( 'Application Approved', 'edd_fes' ) );
				$message = EDD_FES()->helper->get_option( 'fes-vendor-app-approved-email', '' );
				$type = "user";
				$args['permissions'] = 'fes-vendor-app-approved-email-toggle';
				EDD_FES()->emails->send_email( $user->user_email, $from_name, $from_email, $subject, $message, $type, $this->user_id, $args );
				
				do_action('fes_approve_vendor_admin', $this->user_id );
				if ( $output ){
					$output = array();
					$output['title'] = __( 'Success!', 'edd_fes' );
					$output['message'] = sprintf( _x( '%s approved successfully', 'FES uppercase singular setting for vendor', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = true ) );
					$output['redirect_to'] = '#';
					$output['success'] = true;
					return $output;
				} else {
					return;
				}				
			} else if ( $new_status === 'suspended' ){

				// if the current user is not approved, they can't be suspended
				if ( $this->status !== 'approved' ) {
					if ( $output ){
						$output = array();
						$output['title'] = __( 'Error!', 'edd_fes' );
						$output['message'] = __( 'Invalid status change', 'edd_fes' );
						$output['redirect_to'] = '#';
						$output['success'] = false;
						return $output;
					} else {
						return;
					}
				}

				// We're good to go, remove approved status and set to suspended
				$user = new WP_User( $this->user_id );

				// remove all their posts
				$args = array('post_type' => 'download', 'author' => $this->user_id, 'posts_per_page'=> -1, 'fields' => 'ids', 'post_status' => 'any' );
				$query = new WP_Query( $args );
				foreach ( $query->posts as $download ){
					update_post_meta( $download, 'fes_previous_status', get_post_status( $download ) );

					// Make sure products are never entirely deleted when suspending a vendor
					wp_update_post( array( 'ID' => $download, 'post_status' => 'draft' ) );
				}

				if ( ! ( user_can( $this->user_id, 'subscriber' ) ) ) {
					$user->add_role( 'subscriber' );
				}

				if ( user_can( $this->user_id, 'frontend_vendor' ) ) {
					$user->remove_role( 'frontend_vendor' );
				}

				$this->update( $arr );
				/**
				 * Suspended Email Subject
				 *
				 * Change the subject of the suspended vendor application email.
				 *
				 * @since 2.0.0
				 *
				 * @param string $subject Subject of email.
				 */
				$subject    = apply_filters( 'fes_vendor_suspended_message_subj', __( 'Suspended', 'edd_fes' ) );
				$message    = EDD_FES()->helper->get_option( 'fes-vendor-suspended-email', '' );

				EDD_FES()->emails->send_email( $user->user_email, $from_name, $from_email, $subject, $message, 'user', $this->user_id, array( 'fes-vendor-suspended-email-toggle' ) );

				do_action('fes_vendor_suspended_admin', $this->user_id );
				if ( $output ){
					$output = array();
					$output['title'] = __( 'Success!', 'edd_fes' );
					$output['message'] = sprintf( _x( '%s suspended successfully', 'FES uppercase singular setting for vendor', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = true ) );
					$output['redirect_to'] = '#';
					$output['success'] = true;
					return $output;
				} else {
					return;
				}				
			} else if ( $new_status === 'unsuspended' ){
				// this is a psuedo status that will map to approved (it's here so we can do different actions for a vendor going to approved from suspended vs pending)
				// if the vendor isn't suspended, they can't be unsuspended
				if ( $this->status !== 'suspended' ) {
					if ( $output ){
						$output = array();
						$output['title'] = __( 'Error!', 'edd_fes' );
						$output['message'] = __( 'Invalid status change', 'edd_fes' );
						$output['redirect_to'] = '#';
						$output['success'] = false;
						return $output;
					} else {
						return;
					}
				}
			   
				$user = new WP_User( $this->user_id );

				if ( ! ( user_can( $this->user_id, 'frontend_vendor' ) ) ) {
					$user->add_role( 'frontend_vendor' );
				}

				if ( user_can( $this->user_id, 'suspended_vendor' ) ) {
					$user->remove_role( 'suspended_vendor' );
				}

				// since this a psuedo status, we have to map the update to the actual status
				$arr['status'] = 'approved';
				$this->update( $arr );

				// retrieve all their posts
				$args = array('post_type' => 'download', 'author' => $this->user_id, 'posts_per_page'=> -1, 'fields' => 'ids', 'post_status' => array( 'draft', 'pending', 'trash' ) );
				$query = new WP_Query( $args );
				foreach ( $query->posts as $download ){
					$status = get_post_meta( $download, 'fes_previous_status', true );
					if ( ! $status ) {
						$status = 'pending';
					}
					wp_update_post( array( 'ID' => $download, 'post_status' => $status ) );
					wp_untrash_post_comments( $download );
				}

				// Send the email
				/**
				 * Unsuspended Email Subject
				 *
				 * Change the subject of the unsuspended vendor application email.
				 *
				 * @since 2.0.0
				 *
				 * @param string $subject Subject of email.
				 */
				$subject = apply_filters( 'fes_vendor_unsuspended_message_subj', __( 'Unsuspended', 'edd_fes' ) );
				$message = EDD_FES()->helper->get_option( 'fes-vendor-unsuspended-email', '' );
				EDD_FES()->emails->send_email( $user->user_email, $from_name, $from_email, $subject, $message, "user", $this->user_id, array( 'fes-vendor-unsuspended-email-toggle' ) );

				do_action('fes_vendor_unsuspended_admin', $this->user_id );
				if ( $output ){
					$output = array();
					$output['title'] = __( 'Success!', 'edd_fes' );
					$output['message'] = sprintf( _x( '%s unsuspended successfully', 'FES uppercase singular setting for vendor', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = true ) );
					$output['redirect_to'] = '#';
					$output['success'] = true;
					return $output;
				} else {
					return;
				}

			} else if ( $new_status === 'revoked' ){

				if ( $this->status !== 'approved' && $this->status !== 'suspended' ) {
					if ( $output ){
						$output = array();
						$output['title'] = __( 'Error!', 'edd_fes' );
						$output['message'] = __( 'Invalid status change', 'edd_fes' );
						$output['redirect_to'] = '#';
						$output['success'] = false;
						return $output;
					} else {
						return;
					}
				}

				// revoked is different than any other vendor status. This is a destructive status. It removes the vendor products permentantly, as well as 
				// permenently revokes access, and deletes the vendor row for this vendor. Once revoked, the vendor must re-apply from scratch to get 
				// back in. Use with extreme caution. Suggested alternative: consider suspending a vendor instead of revoking them. This will retain a 
				// user account on the site, as a subscriber role.

				// We're good to go. Remove frontend_vendor role and fes_is_admin capability, then add subscriber
				$user = new WP_User( $this->user_id );
				if ( ! ( user_can( $this->user_id, 'subscriber' ) ) ) {
					$user->add_role( 'subscriber' ); // in case frontend_vendor is the only role they have. Puts a hose onto a world that might otherwise be on fire.
				}

				if ( user_can( $this->user_id, 'frontend_vendor' ) ) {
					$user->remove_role( 'frontend_vendor' );
				}

				if ( user_can( $this->user_id, 'suspended_vendor' ) ) {
					$user->remove_role( 'suspended_vendor' );
				}

				// remove all their posts
				$args = array( 'post_type' => 'download', 'author' => $this->user_id, 'posts_per_page'=> -1, 'fields' => 'ids', 'post_status' => 'any' );
				$query = new WP_Query( $args );
				foreach ( $query->posts as $id ){
					wp_delete_post( $id, false );
				}
				/**
				 * Revoked Email Subject
				 *
				 * Change the subject of the revoked vendor application email.
				 *
				 * @since 2.0.0
				 *
				 * @param string $subject Subject of email.
				 */
				$subject = apply_filters( 'fes_application_revoked_message_subj', __( 'Application Revoked', 'edd_fes' ) );
				$message = EDD_FES()->helper->get_option( 'fes-vendor-app-revoked-email', '' );

				EDD_FES()->emails->send_email( $user->user_email, $from_name, $from_email, $subject, $message, "user", $this->user_id, array( 'fes-vendor-app-revoked-email-toggle' ) );
				
				do_action('fes_revoke_vendor_admin', $this->user_id);

				$this->db->delete( $this->id ); // delete vendor row
				if ( $output ){
					$output = array();
					$output['title'] = __( 'Success!', 'edd_fes' );
					$output['message'] = sprintf( _x( '%s revoked successfully', 'FES uppercase singular setting for vendor', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = true ) );
					$output['redirect_to'] = '#';
					$output['success'] = true;
					return $output;
				} else {
					return;
				}
			} else {
				// invalid status
					if ( $output ){
						$output = array();
						$output['title'] = __( 'Error!', 'edd_fes' );
						$output['message'] = __( 'Invalid status!', 'edd_fes' );
						$output['redirect_to'] = '#';
						$output['success'] = false;
						return $output;
					} else {
						return;
					}
			}
		} else {
			if ( $new_status === 'pending' ){
				if ( ! ( user_can( $this->user_id, 'subscriber' ) ) ) {
					$user->add_role( 'subscriber' );
				}
				$this->update( $arr );
			} else if ( $new_status === 'approved' ){
				if ( $this->status !== 'pending' ) {
					return;
				}

				// We're good to go, remove pending status and set to approved
				$user = new WP_User( $this->user_id );
				if ( ! ( user_can( $this->user_id, 'frontend_vendor' ) ) ) {
					$user->add_role( 'frontend_vendor' );
				}

				if ( user_can( $this->user_id, 'pending_vendor' ) ) {
					$user->remove_role( 'pending_vendor' );
				}
				$this->update( $arr );
			} else {
				// invalid status
			}
		}
	}

	/**
	 * Get the notes column for the vendor
	 *
	 * @since  2.3
	 * @return string The Notes for the vendor, non-parsed
	 */
	private function get_raw_notes() {

		$all_notes = $this->db->get_column( 'notes', $this->id );

		return $all_notes;

	}

	/**
	 * Sanitize the data for update/create.
	 *
	 * When passing in data to update or create a vendor with,
	 * this function is called automatically to sanitize the data
	 * based on the format of the columns.
	 *
	 * @since  2.3.0
	 * @access public
	 * 
	 * @param  array $data The data to sanitize
	 * @return array       The sanitized data, based off column defaults
	 */
	private function sanitize_columns( $data ) {

		if ( ! is_object( $this->db ) ){
			$this->db = new FES_DB_Vendors;
		}

		$columns        = $this->db->get_columns();
		$default_values = $this->db->get_column_defaults();

		foreach ( $columns as $key => $type ) {

			// Only sanitize data that we were provided
			if ( ! array_key_exists( $key, $data ) ) {
				continue;
			}

			switch( $type ) {

				case '%s':
					if ( 'email' == $key ) {
						$data[$key] = sanitize_email( $data[$key] );
					} elseif ( 'notes' == $key ) {
						$data[$key] = strip_tags( $data[$key] );
					} else {
						$data[$key] = sanitize_text_field( $data[$key] );
					}
					break;

				case '%d':
					if ( ! is_numeric( $data[$key] ) || (int) $data[$key] !== absint( $data[$key] ) ) {
						$data[$key] = $default_values[$key];
					} else {
						$data[$key] = absint( $data[$key] );
					}
					break;

				case '%f':
					// Convert what was given to a float
					$value = floatval( $data[$key] );

					if ( ! is_float( $value ) ) {
						$data[$key] = $default_values[$key];
					} else {
						$data[$key] = $value;
					}
					break;

				default:
					$data[$key] = sanitize_text_field( $data[$key] );
					break;

			}

		}

		return $data;
	}

}
