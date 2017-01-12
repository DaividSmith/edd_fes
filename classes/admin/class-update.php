<?php
/**
 * FES Update System
 *
 * This file deals with FES's user
 * initiate upgrades
 *
 * @package FES
 * @subpackage Install/Upgrade
 * @since 2.2.0
 *
 * @todo Split upgrade routines off into their 
 *       own files.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { 
	exit;
}

/**
 * FES Upgrade Page registration.
 *
 * Register an upgrade page for FES to 
 * use during user initiated upgrade 
 * routines.
 *
 * @since 2.2.0
 * @access public
 *
 * @return void
 */
function fes_register_upgrades_page() {
	add_submenu_page( null, __( 'FES Upgrades', 'edd_fes' ), __( 'FES Upgrades', 'edd_fes' ), 'install_plugins', 'fes-upgrades', 'fes_upgrades_screen' );
}
add_action( 'admin_menu', 'fes_register_upgrades_page', 10 );

/**
 * FES Upgrade Page screen.
 *
 * Renders the screen shown
 * during an FES upgrade routine.
 *
 * @since 2.2.0
 * @access public
 *
 * @return void
 */
function fes_upgrades_screen() {
	$step   = isset( $_GET['step'] ) ? absint( $_GET['step'] ) : 1;
	$counts = count_users();
	$total  = isset( $counts['total_users'] ) ? $counts['total_users'] : 1;
	$total_steps = round( ( $total / 100 ), 0 );
	?>
	<div class="wrap">
		<h2><?php _e( 'Frontend Submissions - Upgrades', 'edd_fes' ); ?></h2>
		<div id="edd-upgrade-status">
			<p><?php _e( 'The upgrade process is running, please be patient. This could take several minutes to complete.', 'edd_fes' ); ?></p>
			<p><strong><?php printf( __( 'Step %d of approximately %d running', 'edd_fes' ), $step, $total_steps ); ?>
		</div>
		<script type="text/javascript">
			document.location.href = "index.php?edd_action=<?php echo $_GET['edd_upgrade']; ?>&step=<?php echo absint( $_GET['step'] ); ?>";
		</script>
	</div>
<?php	
}

/**
 * FES Show Upgrade Notice.
 *
 * Determines if the FES install needs
 * to run an upgrade routine and if 
 * so shows an admin notice for the user
 * to run it.
 *
 * @since 2.2.0
 * @access public
 *
 * @return void
 */
function fes_show_upgrade_notice() {
	$fes_version = get_option( 'fes_db_version', '2.1' );

	if ( version_compare( $fes_version, '2.2', '<' ) && ! isset( $_GET['edd_upgrade'] ) ) {
		printf(
			'<div class="updated"><p>' . __( 'Vendor permissions need to be updated, click <a href="%s">here</a> to start the upgrade.', 'edd_fes' ) . '</p></div>',
			esc_url( add_query_arg( array( 'edd_action' => 'upgrade_vendor_permissions' ), admin_url() ) )
		);
	}

	if ( version_compare( $fes_version, '2.2', '>=' ) && version_compare( $fes_version, '2.3', '<' ) && ! isset( $_GET['edd_upgrade'] ) ) {
		printf(
			'<div class="updated"><p>' . __( 'The vendor table needs to be updated, click <a href="%s">here</a> to start the upgrade.', 'edd_fes' ) . '</p></div>',
			esc_url( add_query_arg( array( 'edd_action' => 'upgrade_23_upgrade' ), admin_url() ) )
		);
	}
}
add_action( 'admin_notices', 'fes_show_upgrade_notice' );

/**
 * FES 2.2 Vendor Upgrade Permissions.
 *
 * In FES 2.2, we needed to add the role
 * of frontend_vendor to all users who had
 * the capability of fes_is_vendor but not
 * the role.
 *
 * @since 2.2.0
 * @access public
 *
 * @return void
 */
function fes_22_upgrade_vendor_permissions() {

	$fes_version = get_option( 'fes_db_version', '2.1' );

	if ( version_compare( $fes_version, '2.2', '>=' ) ) {
		return;
	}

	ignore_user_abort( true );

	if ( ! edd_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) )
		set_time_limit( 0 );

	$step   = isset( $_GET['step'] ) ? absint( $_GET['step'] ) : 1;
	$offset = $step === 1 ? 0 : $step * 100; 

	$users = new WP_User_Query( array( 'fields' => 'ID', 'number' => 100, 'offset' => $offset ) );
	$users = $users->results;
	if ( $users && count( $users ) > 0 ) {
		foreach( $users as $user => $id ) {
			if ( user_can( $id, 'fes_is_vendor' ) && !user_can( $id, 'fes_is_admin' ) && !user_can( $id,'administrator' ) && !user_can( $id,'editor' ) ){
				$user = new WP_User( $id );
				$user->add_role( 'frontend_vendor' );
			}
		}

		// Keys found so upgrade them
		$step++;
		$redirect = add_query_arg( array(
			'page'        => 'fes-upgrades',
			'edd_upgrade' => 'upgrade_vendor_permissions',
			'step'        => $step
		), admin_url( 'index.php' ) );
		wp_redirect( $redirect ); exit;

	} else {

		// No more keys found, update the DB version and send to welcome page
		update_option( 'fes_db_version', '2.2' );
		wp_redirect( admin_url( 'admin.php?page=fes-about' ) ); exit;
	}

}
add_action( 'edd_upgrade_vendor_permissions', 'fes_22_upgrade_vendor_permissions' );

/**
 * FES 2.3 Vendor Table.
 *
 * In FES 2.3, we needed to add the vendor
 * database table, and add all of the vendors
 * to that table. Once this was done, we 
 * removed the suspended_vendor and 
 * pending_vendor roles.
 *
 * @since 2.3.0
 * @access public
 *
 * @return void
 */
function fes_23_upgrade() {

	$fes_version = get_option( 'fes_db_version', '2.2' );

	if ( version_compare( $fes_version, '2.3', '>=' ) ) {
		return;
	}

	ignore_user_abort( true );

	if ( ! edd_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
		set_time_limit( 0 );
	}

	$step   = isset( $_GET['step'] ) ? absint( $_GET['step'] ) : 1;
	$offset = $step === 1 ? 0 : $step * 100; 

	$users = new WP_User_Query( array( 'fields' => 'ID', 'number' => 100, 'offset' => $offset ) );
	$users = $users->results;
	if ( $users && count( $users ) > 0 ) {
		foreach( $users as $user => $id ) {
			if ( ( user_can( $id, 'frontend_vendor' ) || user_can( $id, 'suspended_vendor' ) || user_can( $id, 'pending_vendor' ) ) || user_can( $id, 'fes_is_admin' ) || user_can( $id,'administrator' ) || user_can( $id,'editor' ) ){
				$user   = new WP_User( $id );
				$status = '';

				if ( user_can( $id, 'pending_vendor' ) ) {
					$status = 'pending';
				} else if ( user_can( $id, 'suspended_vendor' ) ) {
					$status = 'suspended';
				} else if ( user_can( $id, 'frontend_vendor' ) || user_can( $id, 'fes_is_admin' ) || user_can( $id,'administrator' ) || user_can( $id,'editor' ) ) {
					$status = 'approved';			    
				} else{
					$status = false; // not a vendor
				}

				if ( $status ){
					$vendor_products = array();
					$vendor_products = get_posts( array(
						'nopaging'    => true,
						'author'      => $id,
						'orderby'     => 'title',
						'post_type'   => 'download',
						'post_status' => 'publish',
						'order'		  => 'ASC'
					) );

					$product_count = count( $vendor_products );
					$sales_value    = 0.00;
					$sales_count   = 0;

					if ( empty( $vendor_products ) ){
						$sales_value  = 0.00;
						$sales_count = 0;
					} else {
						foreach ( $vendor_products as $product ) {
							$download    = new EDD_Download( $product->ID );
							$sales_value  = $sales_value  + $download->earnings;
							$sales_count = $sales_count + $download->sales;
						}
					}

					$db_user = new FES_DB_Vendors();
					if ( !$db_user->exists( 'email', $user->user_email ) ){
						$db_user->add( array(
							'user_id'        => $user->ID,
							'email'          => $user->user_email,
							'username'       => $user->user_login,
							'name'           => $user->display_name,
							'product_count'  => $product_count,
							'sales_count'	 => $sales_count,
							'sales_value'	 => $sales_value,
							'status'		 => $status,
							'notes'          => '',
							'date_created'   => $user->user_registered,
						) );
					}
				}
			}
		}

		// vendors found so upgrade them
		$step++;
		$redirect = add_query_arg( array(
			'page'        => 'fes-upgrades',
			'edd_upgrade' => 'upgrade_23_upgrade',
			'step'        => $step
		), admin_url( 'index.php' ) );
		wp_redirect( $redirect ); exit;

	} else {

		// No more keys found, update the DB version and finish up
		// remove old roles
		remove_role( 'pending_vendor' );
		remove_role( 'suspended_vendor' );
		update_option( 'fes_db_version', '2.3' );
		wp_redirect( admin_url( 'admin.php?page=fes-about' ) ); exit;
	}

}
add_action( 'edd_upgrade_23_upgrade', 'fes_23_upgrade' );