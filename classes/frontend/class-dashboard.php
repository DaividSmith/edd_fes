<?php
/**
 * FES Dashboard
 *
 * This file deals with the vendor dashboard.
 *
 * @package FES
 * @subpackage Frontend
 * @since 2.0.0
 *
 * @todo Alot of the helper functions
 *       should be in the helper class.
 * @todo A lot of these functions are 
 *       unnecessary or redundant. Let's
 *       fix this in 2.4.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { 
	exit;
}

/**
 * FES Dashboard.
 *
 * Shows the FES vendor dashboard.
 *
 * @since 2.0.0
 * @access public
 */
class FES_Dashboard {
	/**
	 * FES Vendor Dashboard Actions.
	 *
	 * Runs actions required to show
	 * the FES vendor dashboard, and
	 * register the vendor dashboard
	 * shortcode.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @return void
	 */
	function __construct() {
		add_shortcode( 'fes_vendor_dashboard', array( $this, 'display_fes_dashboard' ) );
		add_action( 'template_redirect', array( $this, 'check_access' ) );
		add_action( 'init', array( $this, 'delete_product' ) );
		add_action( 'init', array( $this,'comment_intercept' ) );
		add_action( 'init', array( $this,'mark_comment_as_read' ) );

	}

	/**
	 * FES Check Access.
	 *
	 * Checks to make sure that the person
	 * currently on the vendor dashboard page
	 * is allowed to be on the page they are
	 * on.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @global WP_Post $post Post object of current page.
	 * 
	 * @return void
	 */	
	public function check_access() {
		global $post;
		if ( is_page( EDD_FES()->helper->get_option( 'fes-vendor-dashboard-page', false ) ) && ( has_shortcode( $post->post_content, 'fes_vendor_dashboard' ) ) ) {
			$task = !empty( $_GET['task'] ) ? $_GET['task'] : '';
			if ( $task == 'logout' ) {
				$this->fes_secure_logout();
			}
			if ( !is_admin() && is_user_logged_in() && !EDD_FES()->vendors->user_is_vendor() && !isset( $_GET['view'] ) ) {
				$user_id = get_current_user_id();
				$user = new WP_User( $user_id );
				if ( EDD_FES()->vendors->user_is_status( 'pending' ) ) {
					// are they a pending vendor: display not approved display
					$base_url = get_permalink( EDD_FES()->helper->get_option( 'fes-vendor-dashboard-page', get_permalink() ) );
					$base_url = add_query_arg( 'user_id', $user_id, $base_url );
					$base_url = add_query_arg( 'view', 'pending', $base_url );
					wp_redirect( $base_url );
					exit;
				} else {
					// are they not a vendor yet: show registration page
					$base_url = get_permalink( EDD_FES()->helper->get_option( 'fes-vendor-dashboard-page', get_permalink() ) );
					$base_url = add_query_arg( 'user_id', $user_id, $base_url );
					$base_url = add_query_arg( 'view', 'application', $base_url );
					wp_redirect( $base_url );
					exit;
				}
			}
		}
	}

	/**
	 * FES Display Dashboard.
	 *
	 * Displays the vendor dashboard
	 * page. This is the output of the 
	 * fes_vendor_dashboard shortcode.
	 * 
	 * @since 2.0.0
	 * @access public
	 *
	 * @global WP_Post $post Post object of current page.
	 *
	 * @param  array $atts Extra parameters for the vendor dashboard.
	 * @return string HTML of the vendor dashboard.
	 */	
	public function display_fes_dashboard( $atts ) {
		global $post;
		$default = 'login-register';
		// Note this filter might get removed in 2.4. Use at own risk.
		/**
		 * Default Dashboard logged out view.
		 *
		 * By default a logged out user will see the combo login &
		 * register forms. This filter can change which view a 
		 * logged out user sees by default.
		 *
		 * @since 2.0.0
		 *
		 * @deprecated 2.2.0
		 * 
		 * @param  string $default Default logged out view.
		 */
		$default = apply_filters( 'fes_display_fes_dashboard_default_logged_out_view', $default );
		$view = !empty( $_REQUEST['view'] ) ? $_REQUEST['view'] : $default;

		if ( $view && !EDD_FES()->vendors->user_is_vendor() ) {
			ob_start();
			switch ( $view ) {
				case 'login':
					echo EDD_FES()->forms->render_login_form();
					break;
				case 'register':
					echo EDD_FES()->forms->render_registration_form();
					break;
				case 'login-register':
					echo EDD_FES()->forms->render_login_registration_form();
					break;
				default:
					echo EDD_FES()->forms->render_login_registration_form();
					break;
			}
			return ob_get_clean();
		} else {
			extract( shortcode_atts( array(
				 'user_id' => get_current_user_id()
			), $atts ) );

			//Session set for upload watermarking
			$fes_post_id = isset( $post->ID ) ? $post->ID : '';
			EDD()->session->set( 'fes_dashboard_post_id', $fes_post_id );

			$task = !empty( $_GET['task'] ) ? $_GET['task'] : '';
			ob_start();
			/* Load Menu */
			EDD_FES()->templates->fes_get_template_part( 'frontend', 'menu' );
			echo '<div id="fes-vendor-dashboard">';
			/* Get page options */
			switch ( $task ) {
				case 'dashboard':
					EDD_FES()->templates->fes_get_template_part( 'frontend', 'dashboard' );
					break;
				case 'products':
					global $products;
					$status = isset( $_GET['status'] ) ? $_GET['status'] : false;
					$products = EDD_FES()->vendors->get_products( get_current_user_id(), $status );
					EDD_FES()->templates->fes_get_template_part( 'frontend', 'products' );
					break;
				case 'new-product':
					echo EDD_FES()->forms->render_submission_form();
					break;
				case 'edit-product':
					echo EDD_FES()->forms->render_submission_form();
					break;
				case 'delete-product':
					EDD_FES()->templates->fes_get_template_part( 'frontend', 'delete-product' );
					break;
				case 'earnings':
					EDD_FES()->templates->fes_get_template_part( 'frontend', 'earnings' );
					break;
				case 'orders':
					global $orders;

					// if no permission to view, send to dashboard
					if ( !EDD_FES()->vendors->vendor_can_view_orders() ){
						EDD_FES()->templates->fes_get_template_part( 'frontend', 'dashboard' );
						break;
					}

					$orders = EDD_FES()->vendors->get_all_orders( get_current_user_id(), array() );
					EDD_FES()->templates->fes_get_template_part( 'frontend', 'orders' );
					break;
				case 'edit-order':

					// if no permission to view, send to dashboard
					if ( !EDD_FES()->vendors->vendor_can_view_orders() ){
						EDD_FES()->templates->fes_get_template_part( 'frontend', 'dashboard' );
						break;
					}

					EDD_FES()->templates->fes_get_template_part( 'frontend', 'edit-order' );
					break;
				case 'profile':
					echo EDD_FES()->forms->render_profile_form();
					break;
				case '':
					EDD_FES()->templates->fes_get_template_part( 'frontend', 'dashboard' );
					break;
				default:
					$custom = apply_filters( 'fes_signal_custom_task', false, $task );
					if ( fes_has_key_value( 'task', $task, EDD_FES()->dashboard->get_vendor_dashboard_menu() ) ) {
						do_action( 'fes_custom_task_' . $task );
					} else if ( $custom ){
						do_action( 'fes_custom_task_' . $task );
					} else {
						EDD_FES()->templates->fes_get_template_part( 'frontend', 'dashboard' );
					}
					break;
			}
			echo '</div>';
			return ob_get_clean();
		}
	}

	/**
	 * FES Logout function.
	 *
	 * This function logs out a vendor
	 * and redirects them to the vendor dashboard
	 * logged out default view.
	 * 
	 * @since 2.0.0
	 * @access public
	 *
	 * @return void
	 */	
	public function fes_secure_logout() {
		if ( is_user_logged_in() ) {
			wp_logout();
			$base_url = get_permalink( EDD_FES()->helper->get_option( 'fes-vendor-dashboard-page', get_permalink() ) );
			$base_url = add_query_arg( array(
				'view' => 'login',
				'task' => false
			), $base_url );
			wp_redirect( $base_url );
			exit;
		}
	}

	/**
	 * FES delete product.
	 *
	 * This function deletes a product
	 * on the vendor dashboard if the 
	 * vendor has the right to delete
	 * a particular product.
	 * 
	 * @since 2.0.0
	 * @access public
	 *
	 * @todo Before/After actions.
	 * 
	 * @return void
	 */	
	public function delete_product() {
		if ( ! isset( $_POST['fes_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce($_POST['fes_nonce'], 'fes_delete_nonce') ) {
			return;
		}

		if ( ! isset( $_POST['pid'] ) ) {
			return;
		}

		$post_id = absint( $_POST['pid'] );
		if ( EDD_FES()->vendors->vendor_can_delete_product($post_id) ){
			wp_delete_post( $post_id );
		}

		$redirect_to = get_permalink( EDD_FES()->helper->get_option( 'fes-vendor-dashboard-page', get_permalink() ) );
		$redirect_to = add_query_arg( array(
			'task' => 'products'
		), $redirect_to );
		$redirect_to = apply_filters('fes_delete_product_redirection', $redirect_to, $_POST['pid'] );

		/**
		 * Delete Product (frontend) Action.
		 *
		 * This action runs when a vendor deletes
		 * a product from the frontend.
		 *
		 * @since 2.0.0
		 * @since 2.4.0 Post id is now absint'd coming in.
		 * 
		 * @param  int $post_id Post ID of deleted product.
		 */		
		do_action( 'fes_vendor_delete_product', $post_id );

		wp_redirect( get_permalink( EDD_FES()->helper->get_option( 'fes-vendor-dashboard-page', get_permalink() ) ) );
		exit;
	}

	/**
	 * FES vendor dashboard menu.
	 *
	 * Returns an array of menu items
	 * for the frontend-menu.php template
	 * to process and turn into the vendor
	 * dashboard navigation menu.
	 * 
	 * @since 2.0.0
	 * @access public
	 * 
	 * @return array Menu items to display.
	 */	
	public function get_vendor_dashboard_menu() {
		$menu_items = array();
		$menu_items['home'] = array(
			"icon" => "home",
			"task" => 'dashboard',
			"name" => __( 'Dashboard', 'edd_fes' ),
		);
		$menu_items['my_products'] = array(
			"icon" => "list",
			"task" => 'products',
			"name" => EDD_FES()->helper->get_product_constant_name( $plural = true, $uppercase = true ),
		);
		if ( EDD_FES()->vendors->vendor_can_create_product() ) {
			$menu_items['new_product'] = array(
				"icon" => "pencil",
				"task" => 'new-product',
				"name" => sprintf( _x( 'Add %s', 'FES uppercase singular setting for download', 'edd_fes' ), EDD_FES()->helper->get_product_constant_name( $plural = false, $uppercase = true ) ),
			);
		}
		if ( EDD_FES()->integrations->is_commissions_active() ) {
			$menu_items['earnings'] = array(
				"icon" => "earnings",
				"task" => 'earnings',
				"name" => __( 'Earnings', 'edd_fes' ),
			);
		}
		if ( EDD_FES()->vendors->vendor_can_view_orders() ){
			$menu_items['orders'] = array(
				"icon" => "gift",
				"task" => 'orders',
				"name" => __( 'Orders', 'edd_fes' ),
			);
		}
		$menu_items['profile'] = array(
			"icon" => "user",
			"task" => 'profile',
			"name" => __( 'Profile', 'edd_fes' ),
		);
		$menu_items['logout'] = array(
			"icon" => "off",
			"task" => 'logout',
			"name" => __( 'Logout', 'edd_fes' ),
		);

		/**
		 * Dashboard Menu Items.
		 *
		 * While this filter is commonly used to add new
		 * menu items, it can also be used to edit or delete
		 * menu items from the vendor dashboard page.
		 *
		 * @since 2.0.0
		 * 
		 * @param array $menu_items Menu items for the vendor dashboard.
		 */		
		$menu_items = apply_filters( "fes_vendor_dashboard_menu", $menu_items );

		// This backcompat will be removed in 2.4 as it causes problems for some
		// users.
		if ( ! empty( $menu_items ) && is_array( $menu_items ) ) {
			foreach( $menu_items as $id => $menu_item ){ //backward compat
				if ( isset( $menu_item['task'] ) && is_array( $menu_item['task'] ) ) {
					$menu_items[$id]['task'] = $menu_item['task'][0];
				}
			} 
		}
		return $menu_items;
	}

	/**
	 * Get Product Title.
	 *
	 * This function gets the title of a product.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @param int $product_id Product post ID.
	 * @return string Product Title.
	 */	
	public function product_list_title( $product_id ) {
		$title = esc_html( get_the_title( $product_id ) );
		/**
		 * Get Product Title.
		 *
		 * The product title retrieved by FES for a particular
		 * product post id.
		 *
		 * @since 2.0.0
		 * 
		 * @param string $title The title of the product.
		 * @param int $product_id The post id of the product.
		 */			
		$title = apply_filters( 'fes_product_list_title', $title, $product_id );
		return $title;
	}

	/**
	 * Get Product Url.
	 *
	 * This function gets the url of a product.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @param int $product_id Product post ID.
	 * @return string Product Url.
	 */	
	public function product_list_url( $product_id ) {
		$url = esc_url( get_permalink( $product_id ) );
		/**
		 * Get Product Url.
		 *
		 * The product url retrieved by FES for a particular
		 * product post id.
		 *
		 * @since 2.0.0
		 * 
		 * @param string $url The url of the product.
		 * @param int $product_id The post id of the product.
		 */			
		$url = apply_filters( 'fes_product_list_url', $url, $product_id );
		return $url;
	}

	/**
	 * Get Product Edit Url.
	 *
	 * This function gets the edit url of a product.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @param int $product_id Product post ID.
	 * @return string Product edit url.
	 */
	public function product_list_edit_url( $product_id ) {
		$url = add_query_arg( array( 'task' => 'edit', 'post_id' => $product_id ), get_permalink() );
		return $url;
	}

	/**
	 * Get Product Delete Url.
	 *
	 * This function gets the delete url of a product.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @param int $product_id Product post ID.
	 * @return string Product delete url.
	 */
	public function product_list_delete_url( $product_id ) {
		$url = add_query_arg( array( 'task' => 'delete', 'post_id' => $product_id ), get_permalink() );
		return $url;
	}

	/**
	 * Get Product Status column value.
	 *
	 * This function gets the status of a
	 * product in an HTML span for use on the 
	 * vendor dashboard page.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @param int $product_id Product post ID.
	 * @return string Product status HTML.
	 */
	public function product_list_status( $product_id ) {
		$status = '<span class="download-status ' . EDD_FES()->dashboard->product_list_generate_status( $product_id, true ) . '">' . EDD_FES()->dashboard->product_list_generate_status( $product_id, false ) . '</span>';
		$status = apply_filters( 'fes_product_list_status', $status, $product_id );
		return $status;
	}

	/**
	 * Get Product price column value.
	 *
	 * This function gets the price of a
	 * product for use on the 
	 * vendor dashboard page.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @param int $product_id Product post ID.
	 * @return string Product price.
	 */
	public function product_list_price( $product_id ) {
		if ( edd_has_variable_prices( $product_id ) ){
			$price = edd_price_range( $product_id );
		} else {
			$price = edd_price( $product_id );
		}
		return $price;
	}

	/**
	 * Get Product sales column value.
	 *
	 * This function gets the sales of a
	 * product for use on the 
	 * vendor dashboard page.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @deprecated 2.4.0
	 * @see  FES_Dashboard::product_list_sales_esc()
	 *
	 * @todo  Remove use of this function in FES.
	 * 
	 * @param int $product_id Product post ID.
	 * @return string Product sales.
	 */
	public function product_list_sales( $product_id ) {
		$sales = edd_get_download_sales_stats( $product_id );
		return $sales;
	}

	/**
	 * Get Product sales column value.
	 *
	 * This function gets the sales of a
	 * product for use on the 
	 * vendor dashboard page.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @param int $product_id Product post ID.
	 * @return string Product sales.
	 */
	public function product_list_sales_esc( $product_id ) {
		$sales = esc_html( edd_get_download_sales_stats( $product_id ) );
		return $sales;
	}

	/**
	 * Get Product Actions list.
	 *
	 * This function outputs links
	 * of vendor actions on the dashboard.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @todo  Rewrite how this works.
	 * 
	 * @param int $product_id Product post ID.
	 * @return void
	 */
	public function product_list_actions( $product_id ) {

		if ( 'publish' == get_post_status( $product_id ) ) : ?>
			<a href="<?php echo esc_html( get_permalink( $product_id ) );?>" title="<?php _e( 'View', 'edd_fes' );?>" class="edd-fes-action view-product-fes"><?php _e( 'View', 'edd_fes' );?></a>
		<?php endif; ?>

		<?php if ( EDD_FES()->helper->get_option( 'fes-allow-vendors-to-edit-products', false ) && 'future' != get_post_status( $product_id ) ) : ?>
			<a href="<?php echo add_query_arg( array( 'task' => 'edit-product', 'post_id' => $product_id ), get_permalink() ); ?>" title="<?php _e( 'Edit', 'edd_fes' );?>" class="edd-fes-action edit-product-fes"><?php _e( 'Edit', 'edd_fes' );?></a>
		<?php endif; ?>

		<?php if ( EDD_FES()->helper->get_option( 'fes-allow-vendors-to-delete-products', false ) ) : ?>
			<a href="<?php echo add_query_arg( array( 'task' => 'delete-product', 'post_id' => $product_id ), get_permalink() );?>" title="<?php _e( 'Delete', 'edd_fes' );?>" class="edd-fes-action edit-product-fes"><?php _e( 'Delete', 'edd_fes' );?></a>
		<?php endif;
	}

	/**
	 * Get Product date column value.
	 *
	 * This function gets the date of a
	 * product for use on the 
	 * vendor dashboard page.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @param int $product_id Product post ID.
	 * @return string Product date.
	 */
	public function product_list_date( $product_id ) {
		$post = get_post( $product_id );
		$date = '';
		if ( '0000-00-00 00:00:00' == $post->post_date ) {
			$t_time = $h_time = __( 'Unpublished', 'edd_fes' );
			$time_diff = 0;
		} else {
			$t_time = get_the_time( __( 'Y/m/d g:i:s A', 'edd_fes' ), $post->ID );
			$m_time = $post->post_date;
			$time = get_post_time( 'G', true, $post );

			$time_diff = time() - $time;

			if ( $time_diff > 0 && $time_diff < 24 * 60 * 60 ) {
				$h_time = sprintf( __( '%s ago', 'edd_fes' ), human_time_diff( $time ) );
			} else {
				$h_time = mysql2date( __( 'Y/m/d', 'edd_fes' ), $m_time );
			}
		}

		$date = '<abbr title="' . $t_time . '">' . $h_time . '</abbr><br />';
		if ( 'publish' == $post->post_status ) {
			$date = $date . __( 'Published', 'edd_fes' );
		} elseif ( 'future' == $post->post_status ) {
			$date = $date . __( 'Scheduled', 'edd_fes' );
		} else {
			$date = $date . __( 'Last Modified', 'edd_fes' );
		}
		/**
		 * Get Product Date.
		 *
		 * The product date retrieved by FES for a particular
		 * product post id.
		 *
		 * @since 2.0.0
		 * 
		 * @param string $date The date of the product.
		 * @param int $product_id The post id of the product.
		 */		
		$date = apply_filters( 'fes_product_list_date', $date, $product_id );
		return $date;
	}

	/**
	 * Product List Status Bar.
	 *
	 * Creates status filter for
	 * the vendor dashboard.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @return void
	 */
	public function product_list_status_bar() {
		$statuses = $this->get_product_list_statuses();
		if ( empty( $statuses ) || count( $statuses ) === 1 ) {return;}
		echo '<div class="fes-product-list-status-bar">';
		foreach ( $statuses as $status ) { ?>
			<a href="<?php echo add_query_arg( array( 'status' => $status ) ); ?>" title="<?php echo $this->post_status_to_display( $status ); ?>" class="edit-product-fes"><?php echo $this->post_status_to_display( $status ); ?></a>&nbsp|&nbsp;
		<?php } ?>

			<a href="<?php echo remove_query_arg( array( 'status' ) ); ?>" title="<?php _e( 'All', 'edd_fes' ); ?>" class="edit-product-fes"><?php _e( 'All', 'edd_fes' ); ?></a>
		<?php
		echo '</div>';
	}

	/**
	 * Product list pagination.
	 *
	 * This function creates the pagination
	 * for the products list on the vendor
	 * dashboard.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @return void
	 */
	public function product_list_pagination() {
		$limit = 10;

		$statuses = array( 'pending', 'publish' );

		if ( isset( $_GET['status'] ) && in_array( $_GET['status'], $statuses ) ) {
			$status = $_GET['status'];
		}
		else {
			$status = 'all';
		}

		$order_count = EDD_FES()->vendors->get_all_products_count( get_current_user_id(), $status );
		$num_of_pages = ceil( $order_count / $limit );
		if ( $num_of_pages > 1 ) {
			echo '<div class="fes-product-list-pagination-container">';
			echo paginate_links( array(
					'current' => ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1,
					'format' => '?paged=%#%',
					'total' => $num_of_pages,
					'base' => str_replace( 9999999 , '%#%', str_replace( '#038;', '', get_pagenum_link( 9999999 ) ) ),
					'current' => max( 1, get_query_var( 'paged' ) ),
				) );
			echo '</div>';
		}
	}

	/**
	 * Product list status.
	 *
	 * Generates the status of a particular
	 * product.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @param int $post Product post ID.
	 * @param bool $css For CSS class use.
	 * @return string Product status.
	 */
	public function product_list_generate_status( $post = null, $css = false  ) {
		$post   = get_post( $post );
		$status = $post->post_status;
		if ( $css ) {
			if ( $status == 'publish' ) {
				$status = 'published';
			} else if ( $status == 'expired' ) {
				$status = 'expired';
			} else if ( $status == 'pending' ) {
				$status = 'pending-review';
			} else if ( $status == 'draft' ) {
				$status = 'draft';
			} else if ( $status == 'future' ) {
				$status = 'future';
			} else {
				$status = 'trash';
			}
		} else {
			if ( $status == 'publish' ) {
				$status = __( 'Live', 'edd_fes' );
			} else if ( $status == 'draft' ) {
				$status = __( 'Draft', 'edd_fes' );
			} else if ( $status == 'pending' ) {
				$status = __( 'Pending Review', 'edd_fes' );
			} else if ( $status == 'future' ) {
				$status = __( 'Scheduled', 'edd_fes' );
			} else {
				$status = __( 'Trash', 'edd_fes' );
			}
		}
		return $status;
	}

	/**
	 * Product List Statuses.
	 *
	 * Finds all statuses a vendor
	 * has at least 1 product in.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @todo Optimize this.
	 * 
	 * @return array Product statuses.
	 */
	public function get_product_list_statuses() {
		$user_id = get_current_user_id();
		$statuses = array();

		// Try draft
		$args = array(
			'author' => $user_id,
			'post_type' => 'download',
			'post_status' => 'draft',
			'fields'        => 'ids',
			'posts_per_page' => 1
		);
		$drafts = get_posts( $args );
		if ( count( $drafts ) > 0 ) {
			$statuses[] = 'draft';
		}

		// Try pending
		$args = array(
			'author' => $user_id,
			'post_type' => 'download',
			'post_status' => 'pending',
			'fields'        => 'ids',
			'posts_per_page' => 1
		);
		$pending = get_posts( $args );
		if ( count( $pending ) > 0 ) {
			$statuses[] = 'pending';
		}

		// Try published
		$args = array(
			'author' => $user_id,
			'post_type' => 'download',
			'post_status' => 'publish',
			'fields'        => 'ids',
			'posts_per_page' => 1
		);
		$published = get_posts( $args );
		if ( count( $published ) > 0 ) {
			$statuses[] = 'publish';
		}
		return $statuses;
	}

	/**
	 * Product status to Display.
	 *
	 * String to display based on a 
	 * status string.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @param string $status Product status.
	 * @return string Status display.
	 */
	public function post_status_to_display( $status ) {

		if ( $status == 'publish' ) {
			$status = __( 'Live', 'edd_fes' );
		} else if ( $status == 'draft' ) {
			$status = __( 'Draft', 'edd_fes' );
		} else if ( $status == 'pending' ) {
			$status = __( 'Pending Review', 'edd_fes' );
		} else if ( $status == 'future' ) {
			$status = __( 'Scheduled for Release', 'edd_fes' );
		} else {
			$status = __( 'Trash', 'edd_fes' );
		}
		return $status;
	}

	/**
	 * Order status to Display.
	 *
	 * String to display based on a 
	 * status string.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @param string $status Order status.
	 * @return string Status display.
	 */
	public function order_status_to_display( $status ) {
		if ( $status == 'publish' || $status == '') {
			$status = __( 'Complete', 'edd_fes' );
		}
		elseif ( $status == 'draft' ) {
			$status = __( 'Draft', 'edd_fes' );
		}
		elseif ( $status == 'pending' ) {
			$status = __( 'Pending', 'edd_fes' );
		}
		else {
			$status = __( 'Trash', 'edd_fes' );
		}
		return $status;
	}

	/**
	 * Order list pagination.
	 *
	 * Outputs the pagination on
	 * the orders list on the vendor
	 * dashboard.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @return void
	 */
	public function order_list_pagination() {
		$limit = 10;

		$order_count = EDD_FES()->vendors->get_all_orders_count( get_current_user_id(), array() );
		$num_of_pages = ceil( $order_count / $limit );
		if ( $num_of_pages > 1 ) {
			echo '<div class="fes-order-list-pagination-container">';
			echo paginate_links( array(
					'current' => ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1,
					'format' => '?paged=%#%',
					'total' => $num_of_pages,
					'base' => str_replace( 9999999 , '%#%', str_replace( '#038;', '', get_pagenum_link( 9999999 ) ) ),
					'current' => max( 1, get_query_var( 'paged' ) ),
				) );
			echo '</div>';
		}
	}

	/**
	 * Order list actions.
	 *
	 * Outputs actions that can
	 * be done on an order in the 
	 * order list on the vendor 
	 * dashboard page.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @param int $order_id Order ID.
	 * @return void
	 */
	public function order_list_actions( $order_id ) {
	?>
		<a href="<?php echo add_query_arg( array( 'task' => 'edit-order', 'order_id' => $order_id ), get_permalink() ); ?>" title="<?php _e( 'View', 'edd_fes' );?>" class="view-order-fes"><?php _e( 'View', 'edd_fes' );?></a>
	<?php
	}

	/**
	 * Order list status.
	 *
	 * Outputs order status for an order in the 
	 * order list on the vendor dashboard page.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @param int $order_id Order ID.
	 * @return void
	 */
	public function order_list_status( $order_id ) {
		$order = get_post( $order_id );
		$status = '<span class="order-status">' . EDD_FES()->dashboard->order_status_to_display( $order->status ) . '</span>';
		return $status;
	}

	/**
	 * Order list date.
	 *
	 * Outputs order date for an order in the 
	 * order list on the vendor dashboard page.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @param int $order_id Order ID.
	 * @return string Order list date.
	 */
	public function order_list_date( $order_id ) {
		$post = get_post( $order_id );
		$date = '';

		$t_time = get_the_time( __( 'Y/m/d g:i:s A', 'edd_fes' ) );
		$m_time = $post->post_date;
		$time = get_post_time( 'G', true, $post );

		$time_diff = time() - $time;

		if ( $time_diff > 0 && $time_diff < 24 * 60 * 60 ) {
			$h_time = sprintf( __( '%s ago', 'edd_fes' ), human_time_diff( $time ) );
		} else {
			$h_time = mysql2date( __( 'Y/m/d', 'edd_fes' ), $m_time );
		}
		return $h_time;
	}

	/**
	 * Order list title.
	 *
	 * Outputs order title for an order in the 
	 * order list on the vendor dashboard page.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @param int $order_id Order ID.
	 * @return string Order title.
	 */
	public function order_list_title( $order_id ) {
		$title = __( 'Order: #', 'edd_fes' ) . $order_id;
		return $title;
	}

	/**
	 * Order list total.
	 *
	 * Outputs order total for an order in the 
	 * order list on the vendor dashboard page.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @param int $order_id Order ID.
	 * @return string Order total.
	 */
	public function order_list_total( $order_id ) {
		$total = edd_payment_amount( $order_id );
		return $total;

	}

	/**
	 * Order list customer name.
	 *
	 * Outputs order customer name for an order in the 
	 * order list on the vendor dashboard page.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @param int $order_id Order ID.
	 * @return string Order customer name.
	 */
	public function order_list_customer( $order_id ) {
		$customer = edd_get_payment_meta_user_info( $order_id );
		$customer_name = $customer['first_name'] . ' ' . $customer['last_name'];
		return $customer_name;
	}

	/**
	 * Comment intercept.
	 *
	 * Inserts a vendor response to a commend
	 * left on a vendor product.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @return void
	 */
	public function comment_intercept(){

		if ( ! isset( $_POST['fes_nonce'] ) || ! isset( $_POST['newcomment_body'] ) ) {
			return;
		}

		if ( !wp_verify_nonce($_POST['fes_nonce'], 'fes_comment_nonce') || $_POST['newcomment_body'] === '' ) {
			return;
		}

		$comment_id = absint( $_POST['cid'] );
		$author_id = absint( $_POST['aid'] );
		$post_id = absint( $_POST['pid'] );
		$content = wp_kses( $_POST['newcomment_body'], fes_allowed_html_tags() );

		$user = get_userdata( $author_id );

		update_comment_meta( $comment_id,'fes-already-processed', 'edd_fes' );

		$new_id = wp_insert_comment( array(
			'user_id' => $author_id,
			'comment_author_email' => $user->user_email,
			'comment_author' => $user->user_login,
			'comment_parent' => $comment_id,
			'comment_post_ID' => $post_id,
			'comment_content' => $content
		) );
		// This ensures author replies are not shown in the list
		update_comment_meta( $new_id, 'fes-already-processed', 'edd_fes' );
	}

	/**
	 * Mark comment as read.
	 *
	 * Marks a comment left on a vendor
	 * product as read.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @return void
	 */
	public function mark_comment_as_read(){

		if ( ! isset( $_POST['fes_nonce'] ) || ! wp_verify_nonce( $_POST['fes_nonce'], 'fes_ignore_nonce' ) ) {
			return;
		}

		$comment_id = absint( $_POST['cid'] );
		update_comment_meta( $comment_id, 'fes-already-processed', 'edd_fes');
	}

	/**
	 * Render comments table.
	 *
	 * Renders comments table on the 
	 * vendor dashboard page.
	 *
	 * @since 2.0.0
	 * @since 2.4.0 Removed use of current user and wpdb globals.
	 * @access public
	 *
	 * @todo Let reviews handle this possibly.
	 *
	 * @param int $limit Number of comments to show per page.
	 * @return void
	 */
	public function render_comments_table( $limit ) {
		$user = get_current_user_id();
		$pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) :
		1;
		$offset = ( $pagenum - 1 ) * $limit;
		$args = array(
			'number' => $limit,
			'offset' => $offset,
			'post_author' => $user,
			'post_type' => 'download',
			'status' => 'approve',
			'meta_query' => array(
				array(
					'key' => 'fes-already-processed',
					'compare' => 'NOT EXISTS'
				),
			)
		);
		$comments_query = new WP_Comment_Query;
		$comments = $comments_query->query( $args );

		if ( count( $comments ) == 0 ) {
			echo '<tr><td colspan="4">' . __( 'No Comments Found', 'edd_fes' ) . '</td></tr>';
		}
		foreach ($comments as $comment) {
			$this->render_comments_table_row( $comment );
		}

		$args = array('post_author'  => $user,'post_type'    => 'download','status'       => 'approve','author__not_in' => array( $user ),'meta_query'   => array(array('key' => 'fes-already-processed','compare' => 'NOT EXISTS',),));
		$comments_query = new WP_Comment_Query;
		$comments = $comments_query->query( $args );

		if ( count($comments) > 0){
			$pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) :
			1;
			$num_of_pages = ceil( count($comments) / $limit );
			$page_links = paginate_links( array('base' => add_query_arg( 'pagenum', '%#%' ),'format' => '','prev_text' => __( '&laquo;', 'aag' ),'next_text' => __( '&raquo;', 'aag' ),'total' => $num_of_pages,'current' => $pagenum) );

			if ( $page_links ) {
				echo '<div class="fes-pagination">' . $page_links . '</div>';
			}
		}
	}

	/**
	 * Render comments table row.
	 *
	 * Renders comments table row on the 
	 * vendor dashboard page.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @todo Let reviews handle this possibly.
	 *
	 * @param WP_Post $comment Current comment post object.
	 * @return void
	 */
	public function render_comments_table_row( $comment ) {
		$comment_date = get_comment_date( 'Y/m/d \a\t g:i a', $comment->comment_ID );
		$comment_author_img = EDD_FES()->vendors->get_avatar( 'comment_author_image', $comment->comment_author_email, 128 );
		$purchased = edd_has_user_purchased( $comment->user_id, $comment->comment_post_ID );
		?>
		<tr>
			<td class="col-author" style="width:25%;">
				<p class="fes-author-img fes-comment-table-meta"><?php echo $comment_author_img; ?></p>
				<p id="fes-comment-author" class="fes-comment-table-meta">
					<span class="fes-comment-author-name"><?php echo $comment->comment_author; ?></span>
					<?php
					if ($purchased){
						echo '<strong class="fes-purchase-badge fes-purchase-badge-purchased fes-light-green">'.__('Has Purchased','edd_fes').'</strong>';
					} else {
						echo '<strong class="fes-purchase-badge fes-purchase-badge-not-purchased fes-light-red">'.__('Has Not Purchased','edd_fes').'</strong>';
					}
					?>
				</p>
				<p id="fes-comment-date" class="fes-comment-table-meta"><?php echo $comment_date; ?></p>
				<p id="fes-product-name" class="fes-comment-table-meta">
					<strong><?php echo EDD_FES()->helper->get_product_constant_name( $plural = false, $uppercase = true ) . ': '; ?></strong>
					<a href="<?php echo esc_url( get_permalink( $comment->comment_post_ID ) ); ?>"><?php echo get_the_title( $comment->comment_post_ID ); ?></a>
				</p>
				<p id="fes-view-comment" class="fes-comment-table-meta">
					<a href="<?php echo esc_url( get_permalink( $comment->comment_post_ID ) . '#comment-' . $comment->comment_ID ); ?>"><?php _e( 'View Comment','edd_fes' ); ?></a>
				</p>
			</td>
			<td class="col-content" style="width:70%;">
				<div class="fes-comments-content"><?php echo $comment->comment_content; ?></div>
				<hr/>
				<div id="<?php echo $comment->comment_post_ID; ?>" class="fes-vendor-comment-respond-form">
					<span><?php _e( 'Respond:', 'edd_fes' ); ?></span>
					<table>
						<tr>
							<form id="fes_comments-form" action="" method="post">
								<input type="hidden" name="cid" value="<?php echo $comment->comment_ID; ?>">
								<input type="hidden" name="pid" value="<?php echo $comment->comment_post_ID; ?>">
								<input type="hidden" name="aid" value="<?php echo get_current_user_id(); ?>">
								<?php wp_nonce_field('fes_comment_nonce', 'fes_nonce'); ?>
								<textarea class="fes-cmt-body" name="newcomment_body" cols="50" rows="8"></textarea>
								<button class="fes-cmt-submit-form button" type="submit"><?php  _e( 'Post Response', 'edd_fes' ); ?></button>
							</form>
							<form id="fes_ignore-form" action="" method="post">
								<input type="hidden" name="cid" value="<?php echo $comment->comment_ID; ?>">
								<?php wp_nonce_field('fes_ignore_nonce', 'fes_nonce'); ?>
								<button class="fes-ignore button" type="submit"><?php _e( 'Mark as Read', 'edd_fes' ); ?></button>
							</form>
						</tr>
					</table>
				</div>
			</td>
		</tr>
		<?php
	}

}
