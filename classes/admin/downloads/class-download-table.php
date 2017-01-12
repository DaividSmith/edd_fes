<?php
/**
 * Download Table Functionality
 *
 * Runs actions and filters on the download
 * table created by EDD in the admin to make
 * it easier to manage vendor submissions
 *
 * @package FES
 * @subpackage Administration
 * @since 2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { 
	exit;
}

/**
 * FES Download Table Helper
 *
 * This class contains actions and filters run on the 
 * download table in the backend
 *
 * @since 2.0.0
 * @access public
 */
class FES_Download_Table {

	/**
	 * Registers all actions and filters for the admin download table.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function __construct() {
		add_filter( 'manage_edit-download_columns', array( $this, 'columns' ) );
		add_action( 'manage_download_posts_custom_column', array(  $this, 'custom_columns'  ), 2 );
		add_action( 'admin_footer-edit.php', array( $this, 'add_bulk_actions' ) );
		add_action( 'load-edit.php', array( $this, 'do_bulk_actions'  ) );
		add_action( 'admin_init', array( $this, 'approve_download'  ) );
		add_action( 'admin_notices', array( $this, 'approved_notice'  ) );
		add_filter( 'post_row_actions', array( $this, 'remove_quick_edit' ), 10, 2 );
		add_filter( 'list_table_primary_column', array( $this, 'primary_column' ), 10, 2 );
	}
	
	/**
	 * Remove quick edit.
	 *
	 * Removes quick edit and other inline row actions from the download table.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @global WP_Post $post Post object for the current row (provided by WordPress core).
	 * @param  array $actions Array containing the post row actions.
	 * @return array Actions array after quick edit is removed.
	 */
	public function remove_quick_edit( $actions ) {
		global $post;
		if ( is_object( $post) && $post->post_type === 'fes-forms' ) {
			if ( isset( $actions['inline hide-if-no-js'] ) ){
				unset( $actions['inline hide-if-no-js'] );
			}
		}
		return $actions;
	}

	public function primary_column( $column, $screen ) {
		if ( 'edit-download' === $screen ) {
			$column = 'product';
		}
	 
		return $column;
	}

	/**
	 * Remove quick edit.
	 *
	 * Removes quick edit and other inline row actions from the download table.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @global string $post_type Post type for the current post table (provided by WordPress core).
	 * @return void
	 */
	public function add_bulk_actions() {
		global $post_type;
		if ( $post_type === 'download' ) { ?>
			<script type="text/javascript">
			  jQuery(document).ready(function() {
				jQuery('<option>').val('approve_downloads').text('<?php _e( 'Approve Downloads', 'edd_fes' ); ?>').appendTo("select[name='action']");
				jQuery('<option>').val('approve_downloads').text('<?php	_e( 'Approve Downloads', 'edd_fes' ); ?>').appendTo("select[name='action2']");
			  });
			</script>
			<?php
		}
	}
	
	/**
	 * Admin download table bulk actions.
	 *
	 * Runs the bulk action selected on the admin download table.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @uses  FES_List_Table::current_action Retrieves the bulk action selected.
	 * @uses  FES_Emails::send_email Sends the email after approving a download.
	 * 
	 * @return void
	 */
	public function do_bulk_actions() {
		$wp_list_table = new FES_List_Table();
		$action        = $wp_list_table->current_action();
		switch ( $action ) {
			case 'approve_downloads':
				check_admin_referer( 'bulk-posts' );
				$post_ids           = array_map( 'absint', array_filter( (array) $_GET['post'] ) );
				$approved_downloads = array();
				if ( !empty( $post_ids ) ) {
					foreach ( $post_ids as $post_id ) {
						$download_data = array(
							'ID' 		  => $post_id,
							'post_status' => 'publish' 
						);

						if ( $post_id < 1 ){
							continue;
						}

						if ( get_post_status( $post_id ) === 'pending' && wp_update_post( $download_data ) ){
							$approved_downloads[] = $post_id;
						}
						$post = get_post( $post_id );

						if ( !is_object( $post ) || is_wp_error( $post ) ){
							continue;
						}

						$user = new WP_User( $post->post_author );

						if ( !is_object( $user ) || is_wp_error( $user ) ){
							continue;
						}

						$to 		= $user->user_email;
						$from_name  = edd_get_option( 'from_name', get_bloginfo( 'name' ) );
						$from_email = edd_get_option( 'from_email', get_bloginfo( 'admin_email' ) );

						/**
						 * Filter submission accepted email subject.
						 *
						 * Allows someone to change the subject of the email
						 * sent when a download is approved in the admin.
						 *
						 * @since 2.0.0
						 *
						 * @param string  $subject Subject for the message.
						 */
						$subject = apply_filters( 'fes_submission_accepted_message_subj', __( 'Submission Accepted', 'edd_fes' ) );

						$message = EDD_FES()->helper->get_option( 'fes-vendor-submission-approved-email', '' );
						$type 	 = "post";
						$id 	 = $post->ID;
						$args['permissions'] = 'fes-vendor-submission-approved-email-toggle';
						EDD_FES()->emails->send_email( $to , $from_name, $from_email, $subject, $message, $type, $id, $args );

						/**
						 * Action after download approved in admin.
						 *
						 * Allows someone to run an action after a submission
						 * is approved in the admin download table.
						 *
						 * @since 2.0.0
						 *
						 * @param int  $post_id Post ID of the approved download.
						 */
						do_action( 'fes_approve_download_admin', $post_id );
					}
				}
				wp_redirect( remove_query_arg( 'approve_downloads', add_query_arg( 'approved_downloads', $approved_downloads, admin_url( 'edit.php?post_type=download' ) ) ) );
				exit;
				break;
		}
		return;
	}

	/**
	 * Admin download table approve download.
	 *
	 * Approves a download when a accept submission button is pushed.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @uses  FES_Emails::send_email Sends the email after approving a download.
	 * 
	 * @return void
	 */
	public function approve_download() {
		if ( !empty( $_GET['approve_download'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'approve_download' ) && current_user_can( 'edit_post', $_GET['approve_download'] ) ) {

			$post_id       = absint( $_GET['approve_download'] );
			$download_data = array(
				 'ID'         => $post_id,
				'post_status' => 'publish' 
			);

			if ( $post_id < 1 ){
				return;
			}

			wp_update_post( $download_data );

			$post = get_post( $post_id );

			if ( ! is_object( $post ) || is_wp_error( $post ) ){
				return;
			}

			$user = new WP_User( $post->post_author );

			if ( !is_object( $user ) || is_wp_error( $user ) ){
				return;
			}			

			$to 		= $user->user_email;
			$from_name  = edd_get_option( 'from_name', get_bloginfo( 'name' ) );
			$from_email = edd_get_option( 'from_email', get_bloginfo( 'admin_email' ) );

			/** This filter is documented above. */
			$subject = apply_filters( 'fes_submission_accepted_message_subj', __( 'Submission Accepted', 'edd_fes' ) );
			
			$message = EDD_FES()->helper->get_option( 'fes-vendor-submission-approved-email', '' );
			$type 	 = "post";
			$id 	 = $post->ID;
			$args['permissions'] = 'fes-vendor-submission-approved-email-toggle';
			EDD_FES()->emails->send_email( $to , $from_name, $from_email, $subject, $message, $type, $id, $args );

			/** This action is documented above. */
			do_action( 'fes_approve_download_admin', $post_id );
			
			wp_redirect( remove_query_arg( 'approve_download', add_query_arg( 'approved_downloads', $post_id, admin_url( 'edit.php?post_type=download' ) ) ) );
			exit;
		}
	}

	/**
	 * Download approved admin notice.
	 *
	 * Shows the "download(s) approved" admin notice after accepting a vendor submission.
	 * in the admin.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @global  $post_type The post type of the current post, provided by WordPress core.
	 * @global  $pagenow The current page, provided by WordPress core.
	 * 
	 * @return void
	 */
	public function approved_notice() {
		global $post_type, $pagenow;
		if ( $pagenow == 'edit.php' && $post_type == 'download' && !empty( $_REQUEST['approved_downloads'] ) ) {
			$approved_downloads = $_REQUEST['approved_downloads'];
			if ( is_array( $approved_downloads ) ) {
				$approved_downloads = array_map( 'absint', $approved_downloads );
				$titles             = array();

				if ( empty( $approved_downloads ) ){
					return;
				}

				foreach ( $approved_downloads as $download_id ){
					$titles[] = get_the_title( $download_id );
				}
				echo '<div class="updated"><p>' . sprintf( _x( '%s approved', 'Titles of downloads approved', 'edd_fes' ), '&quot;' . implode( '&quot;, &quot;', $titles ) . '&quot;' ) . '</p></div>';
			} else {
				echo '<div class="updated"><p>' . sprintf( _x( '%s approved', 'Title of download apporved', 'edd_fes' ), '&quot;' . get_the_title( $approved_downloads ) . '&quot;' ) . '</p></div>';
			}
		}
	}

	/**
	 * Download table columns.
	 *
	 * Sets and alters the download list table columns.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @uses  FES_Vendors::user_is_admin Hide the sales and earnings columns from non-admins.
	 *
	 * @param  $columns Columns of the download list table.
	 * @return array Columns for the download list table.
	 */
	public function columns( $columns ) {
		$columns               = array();
		$columns[ "cb" ]       = "<input type=\"checkbox\" />";
		$columns[ "product" ]  = EDD_FES()->helper->get_product_constant_name( $plural = false, $uppercase = true );
		$columns[ "status" ]   = __( "Status", "edd_fes" );
		$columns[ "author" ]   = EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = true );
		$columns[ "price" ]    = __( "Price", "edd_fes" );
		$columns[ "sales" ]    = __( "Sales", "edd_fes" );
		$columns[ "earnings" ] = __( "Earnings", "edd_fes" );
		$columns[ "date" ]     = __( "Date", "edd_fes" );

		if ( !EDD_FES()->vendors->user_is_admin() ) {
			if ( isset( $columns['sales'] ) ){
				unset( $columns['sales'] );				
			}
			if ( isset( $columns['earnings'] ) ){
				unset( $columns['earnings'] );				
			}
		}

		/**
		 * Download list table column filter.
		 *
		 * Allows someone to add/remove/edit/reorder the columns
		 * of the download list table, after FES customizes it.
		 *
		 * @since 2.0.0
		 *
		 * @param array  $columns Columns of the download list table.
		 */
		return apply_filters( 'fes_download_table_columns', $columns );
	}

	/**
	 * Download table custom column values.
	 *
	 * Sets the values for the custom columns FES adds to the admin download list table.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @uses  FES_Vendors::user_is_admin To hide the admin actions from non-vendors.
	 *
	 * @param  $column Current column being filled.
	 * @return string Value of the column.
	 */
	public function custom_columns( $column ) {
		global $post;

		if ( !is_object( $post ) ){
			echo '';
		}

		$post_id = $post->id;
		$output = '';

		switch ( $column ) {
			case "status":
				$output .= '<span class="download-status ' . $this->get_the_download_status( $post, true ) . '">' . $this->get_the_download_status( $post, false ) . '</span>';
				break;
			case "product":
				$output .= sprintf( '<strong><a class="post-edit-link" href="%s">#%s – %s</a></strong>', get_edit_post_link( $post->ID ), $post->ID, $post->post_title );
				if ( EDD_FES()->vendors->user_is_admin() ) {
					$admin_actions = array();
					$output .= '<br />';
					if ( $post->post_status !== 'trash' ) {
						$admin_actions['view']   = array(
							 'action' => 'view',
							'name' => __( 'View', 'edd_fes' ),
							'url' => get_permalink( $post->ID ) 
						);
						$admin_actions['edit']   = array(
							 'action' => 'edit',
							'name' => __( 'Edit', 'edd_fes' ),
							'url' => get_edit_post_link( $post->ID ) 
						);
						if ( $post->post_status !== 'publish' ){
							$admin_actions['delete'] = array(
								'action' => 'revoked',
								'name' => __( 'Delete', 'edd_fes' ),
								'url' => get_delete_post_link( $post->ID ) 
							);
						} else{
							$admin_actions['revoke'] = array(
								'action' => 'revoked',
								'name' => __( 'Revoke', 'edd_fes' ),
								'url' => get_delete_post_link( $post->ID ) 
							);
						}
					}
					if ( $post->post_status == 'pending' && current_user_can( 'publish_posts' ) ) {
						$admin_actions['approve'] = array(
							'action' => 'approved',
							'name' => __( 'Approve', 'edd_fes' ),
							'url' => wp_nonce_url( add_query_arg( 'approve_download', $post->ID ), 'approve_download' ) 
						);
					}

					/**
					 * Admin actions for the current download.
					 *
					 * Allows one to filter the admin actions that are allowed for each
					 * download in the admin download list table.
					 *
					 * @since 2.0.0
					 *
					 * @param array $admin_actions Admin actions for the current download.
					 * @param WP_Post $post WP_Post object of the current post.
					 */
					$admin_actions = apply_filters( 'fes_download_table_actions', $admin_actions, $post );
					
					foreach ( $admin_actions as $action ) {
						$image = isset( $action['image_url'] ) ? $action['image_url'] : fes_plugin_url . 'assets/img/icons/' . $action['action'] . '.png';
						$output .= sprintf( '<a class="button tips" href="%s" data-tip="%s"><img src="%s" alt="%s" width="14" /></a>', esc_url( $action['url'] ), esc_attr( $action['name'] ), esc_attr( $image ), esc_attr( $action['name'] ) );
					}
				}
				break;
			case 'custom':

				/**
				 * Custom admin download list table column value.
				 *
				 * If a custom column is added to the admin download list table
				 * this filter can be used to fill in it's value.
				 *
				 * @since 2.0.0
				 *
				 * @param WP_Post $post WP_Post object of the current post.
				 */			
				$output .= apply_filters( 'fes_admin_column_values', '', $post );
				break;
			default:
				$output .= '';
				break;
		}

		/**
		 * Admin download list table column value output filter.
		 *
		 * For each custom column in the admin list table, this filter
		 * can be used to filter it's return value.
		 *
		 * @since 2.0.0
		 *
		 * @param WP_Post $post WP_Post object of the current post.
		 */	
		$output = apply_filters( 'fes_download_table_value_' . $column, $output );
		echo $output;
	}
	
	/**
	 * Get the download status.
	 *
	 * This is used in the admin download list table to retrive the current status
	 * of a download (for use within functions), and to also get the css classes for 
	 * the download row.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @uses  FES_Vendors::user_is_admin To hide the admin actions from non-vendors.
	 *
	 * @param  $post Post object or post_id of the post to get the status of.
	 * @param  $css Whether to return the CSS class instead.
	 * @return string Status of the download (or the css class requested).
	 */
	public function get_the_download_status( $post = null, $css = false ) {
		/* In case we got a post_id instead of a post object, attempt to retrieve 
		 * the post object */
		$post   = get_post( $post );

		if ( empty( $post ) || !is_object( $post ) || is_wp_error( $post ) ){
			return '';
		}

		$status = $post->post_status;

		/* If we want the CSS class instead which is used on the admin
		 * download list table to make the color on the status column */
		if ( $css ) {
			if ( $status === 'publish' ){
				$status = 'published';
			} else if ( $status == 'expired' ){
				$status = 'expired';
			} else if ( $status === 'pending' ){
				$status = 'pending-review';
			} else if ( $status === 'draft' ){
				$status = 'draft';
			} else if ( $status === 'future' || $status === 'private' ){
				$status = 'scheduled';
			} else {
				$status = 'trash';
			}
		} else { 
			if ( $status === 'publish' ){
				$status = __( 'Live', 'edd_fes' );
			} else if ( $status === 'draft' ){
				$status = __( 'Draft', 'edd_fes' );
			} else if ( $status === 'pending' ){
				$status = __( 'Pending Review', 'edd_fes' );
			} else if ( $status === 'private' ) {
				$status = __( 'Private', 'edd_fes' );
			} else if ( $status === 'future' ){
				$status = __( 'Scheduled', 'edd_fes' );
			} else {
				$status = __( 'Trash', 'edd_fes' );
			}
		}

		/**
		 * Admin download list table column value output filter.
		 *
		 * For each custom column in the admin list table, this filter
		 * can be used to filter it's return value.
		 *
		 * @since 2.0.0
		 *
		 * @param string $status Status of the download or CSS class requested.
		 * @param WP_Post $post WP_Post object of the current post.
		 * @param bool $css If true has the css class, else has the status l10n'd.
		 */			
		return apply_filters( 'fes_get_the_download_status', $status, $post, $css );
	}
}