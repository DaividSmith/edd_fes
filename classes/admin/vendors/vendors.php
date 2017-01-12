<?php
/**
 * Vendor Page Actions.
 *
 * This file contains functions used to perform
 * actions on the FES vendor admin profiles.
 *
 * @package FES
 * @subpackage Administration
 * @since 2.3.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { 
	exit;
}

/**
 * Vendors Page.
 *
 * Renders the vendors page contents.
 *
 * @since 2.3.0
 * @access public
 * 
 * @return void
 */
function fes_vendors_page() {
	$default_views = fes_vendor_views();
	$requested_view = isset( $_GET['view'] ) ? sanitize_text_field( $_GET['view'] ) : 'vendors';
	if ( array_key_exists( $requested_view, $default_views ) && function_exists( $default_views[$requested_view] ) ) {
		fes_render_vendor_view( $requested_view, $default_views );
	} else {
		fes_vendors_list();
	}
}

/**
 * Register the views for vendor management.
 *
 * Registers the views for the
 * admin vendor profile page.
 *
 * @since 2.3.0
 * @access public
 * 
 * @return array Array of views and their callbacks
 */
function fes_vendor_views() {
	$views = array();
	/**
	 * Admin Vendor Profile Views.
	 *
	 * Array of vendor profile views
	 * for the admin vendor profile.
	 *
	 * @since 2.3.0
	 *
	 * @param  array $views FES vendor views.
	 */	
	return apply_filters( 'fes_vendor_views', $views );
}

/**
 * Register the tabs for vendor management.
 *
 * Registers the tabs for the
 * admin vendor profile page.
 *
 * @since 2.3.0
 * @access public
 * 
 * @return array Array of tabs and their callbacks
 */
function fes_vendor_tabs() {
	$tabs = array();
	/**
	 * Admin Vendor Profile Tabs.
	 *
	 * Array of vendor profile tabs
	 * for the admin vendor profile.
	 *
	 * @since 2.3.0
	 *
	 * @param  array $tabs FES vendor tabs.
	 */
	return apply_filters( 'fes_vendor_tabs', $tabs );

}

/**
 * List table of vendors.
 *
 * Creates the vendor list table
 * in the admin.
 *
 * @since 2.3.0
 * @access public
 * 
 * @return void
 */
function fes_vendors_list() {
	$vendors_table = new FES_Vendor_Table();
	$vendors_table->prepare_items();
	$view = isset( $_GET['view'] ) ? sanitize_text_field( $_GET['view'] ) : 'all'; ?>
	<div class="wrap">
		<h2 id="fes-vendor-edit-page" ><?php printf( _x( 'FES %s', 'FES uppercase plural setting for vendor', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = true, $uppercase = true ) ); ?></h2>
		<?php do_action( 'fes_vendors_table_top' ); ?>
		<form id="fes-vendors-filter" method="get" action="<?php echo admin_url( 'admin.php?page=fes-vendors' ); ?>">
			<?php
			$vendors_table->views();
			$vendors_table->search_box( sprintf( _x( 'Search %s', 'FES uppercase plural setting for vendor', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = true, $uppercase = true ) ), 'fes-vendors' );
			$vendors_table->display(); ?>
			<input type="hidden" name="page" value="fes-vendors" />
			<input type="hidden" name="view" value="<?php echo $view ?>" />
		</form>
		<?php do_action( 'fes_vendors_table_bottom' ); ?>
	</div>
	<?php
}

/**
 * Renders the vendor view wrapper.
 *
 * Is the div wrapper around the entire
 * admin vendor profile.
 *
 * @since 2.3.0
 * @access public
 * 
 * @param string  $view      The View being requested.
 * @param array   $callbacks The Registered views and their callback functions.
 * @return void
 */
function fes_render_vendor_view( $view, $callbacks ) {

	if ( ! isset( $_GET['id'] ) || ! is_numeric( $_GET['id'] ) ) {
		edd_set_error( 'fes-invalid_vendor', sprintf( _x( 'Invalid %s ID Provided.', 'FES lowercase singular setting for vendor', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = false ) ) );
	} else{
		$vendor_id = (int) $_GET['id'];
		$vendor    = new FES_Vendor( $vendor_id );
		$db_user = new FES_DB_Vendors();
		if ( !$db_user->exists( 'id', $vendor->id ) ) {
			edd_set_error( 'fes-invalid_vendor', sprintf( _x( 'Invalid %s ID Provided.', 'FES lowercase singular setting for vendor', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = false ) ) );
		}
	}

	$vendor_tabs = fes_vendor_tabs(); ?>

	<div class='wrap'>
		<h2><?php sprintf( _x( '%s Details', 'FES uppercase singular setting for vendor', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = true ) ); ?></h2>
		<?php if ( edd_get_errors() ) :?>
			<div class="error settings-error">
				<?php edd_print_errors(); ?>
			</div>
		<?php 
		return;
		endif; ?>

		<?php if ( $vendor ) : ?>

			<div id="vendor-tab-wrapper">
				<ul id="vendor-tab-wrapper-list">
				<?php foreach ( $vendor_tabs as $key => $tab ) : ?>
					<?php $active = $key === $view ? true : false; ?>
					<?php $class  = $active ? 'active' : 'inactive'; ?>

					<?php if ( ! $active ) : ?>
					<a title="<?php echo esc_attr( $tab['title'] ); ?>" aria-label="<?php echo esc_attr( $tab['title'] ); ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=fes-vendors&view=' . $key . '&id=' . $vendor_id ) ); ?>">
					<?php endif; ?>

					<li class="<?php echo sanitize_html_class( $class ); ?>"><span class="dashicons <?php echo sanitize_html_class( $tab['dashicon'] ); ?>"></span></li>

					<?php if ( ! $active ) : ?>
					</a>
					<?php endif; ?>

				<?php endforeach; ?>
				</ul>
			</div>

			<div id="edd-vendor-card-wrapper" style="float: left">
				<?php $callbacks[$view]( $vendor ) ?>
			</div>

		<?php endif; ?>

	</div>
	<?php

}


/**
 * View a vendor.
 *
 * Creates the "home" page
 * for the admin vendor profile
 * page so to speak.
 *
 * @since 2.3.0
 * @access public
 * 
 * @param FES_Vendor $vendor The Vendor object being displayed
 * @return void
 */
function fes_vendors_view( $vendor ) { ?>

	<?php do_action( 'fes_vendor_card_top', $vendor ); ?>

	<div class="info-wrapper vendor-section">

		<form id="edit-vendor-info" method="post" action="<?php echo admin_url( 'admin.php?page=fes-vendors&view=overview&id=' . $vendor->id ); ?>">

			<div class="vendor-info">

				<div class="avatar-wrap left" id="vendor-avatar">
					<?php echo get_avatar( $vendor->email ); ?><br />

					<span class="info-item editable vendor-edit-link"><a title="<?php printf( _x( 'Edit %s', 'FES uppercase singular setting for vendor', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = true ) ); ?>" href="#" id="edit-vendor"><?php printf( _x( 'Edit %s', 'FES uppercase singular setting for vendor', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = true ) ) ?></a></span>
					<?php
					$user_id = $vendor->user_id;
					$admin_actions = array();
					if ( EDD_FES()->vendors->user_is_status( 'approved', $user_id ) ) {
						$admin_actions['revoke'] = array(
							'action' => 'revoked',
							'label' => __( 'Revoke', 'edd_fes' ),
							'name' => sprintf( __( 'Revoke (and delete all %s of)', 'edd_fes' ), EDD_FES()->helper->get_product_constant_name( $plural = true, $uppercase = false ) ),
							'url' => '#'
						);
						$admin_actions['suspend'] = array(
							'action' => 'suspended',
							'name' => __( 'Suspend', 'edd_fes' ),
							'label' => __( 'Suspend', 'edd_fes' ),
							'url' => '#'
						);
					} else if ( EDD_FES()->vendors->user_is_status( 'pending', $user_id ) ) {
						$admin_actions['approve'] = array(
							'action' => 'approved',
							'name' => __( 'Approve', 'edd_fes' ),
							'label' => __( 'Approve', 'edd_fes' ),
							'url' => '#'
						);
						$admin_actions['decline'] = array(
							'action' => 'declined',
							'name' => __( 'Decline', 'edd_fes' ),
							'label' => __( 'Decline', 'edd_fes' ),		                    
							'url' => '#'
						);
					} else if ( EDD_FES()->vendors->user_is_status( 'suspended', $user_id ) ) {
						$admin_actions['revoke'] = array(
							'action' => 'revoked',
							'label' => __( 'Revoke', 'edd_fes' ),
							'name' => sprintf( __( 'Revoke (and delete all %s of)', 'edd_fes' ), EDD_FES()->helper->get_product_constant_name( $plural = true, $uppercase = false ) ),
							'url' => '#'
						);
						$admin_actions['unsuspend'] = array(
							'action' => 'unsuspended',
							'name' => __( 'Unsuspend', 'edd_fes' ),
							'label' => __( 'Unsuspend', 'edd_fes' ),      
							'url' => '#'
						);
					}
					/** Filter documented in classes/admin/vendors/class-vendor-table.php **/
					$admin_actions = apply_filters( 'fes_admin_actions', $admin_actions, $vendor );
					$data = '';
					foreach ( $admin_actions as $action ) {
						$image = isset( $action['image_url'] ) ? $action['image_url'] : fes_plugin_url . 'assets/img/icons/' . $action['action'] . '.png';
						$data .= sprintf( '<a class="button tips vendor-change-status" data-vendor="%d" data-status="%s" data-nstatus="%s" href="%s" data-tip="%s">%s</a>', (int) esc_attr( $user_id ), esc_attr( $action['action'] ), esc_attr( $action['name'] ), esc_url( $action['url'] ), esc_attr( $action['name'] ), esc_attr( $action['label'] ) );
					}
					echo $data;
					?>
				</div>

				<div class="vendor-id right">
					#<?php echo $vendor->id; ?>
				</div>

				<div class="vendor-address-wrapper right">
				<?php if ( isset( $vendor->user_id ) && $vendor->user_id > 0 ) : ?>

					<?php
					$address = get_user_meta( $vendor->user_id, '_fes_vendor_address', true );
					$defaults = array(
						'line1'   => '',
						'line2'   => '',
						'city'    => '',
						'state'   => '',
						'country' => '',
						'zip'     => ''
					);

					$address = wp_parse_args( $address, $defaults );
					?>

					<?php if ( ! empty( $address ) ) : ?>

					<strong><?php printf( _x( '%s Address', 'FES uppercase singular setting for vendor', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = true ) ); ?></strong>
					<span class="vendor-address info-item editable">
						<span class="info-item" data-key="line1"><?php echo $address['line1']; ?></span>
						<span class="info-item" data-key="line2"><?php echo $address['line2']; ?></span>
						<span class="info-item" data-key="city"><?php echo $address['city']; ?></span>
						<span class="info-item" data-key="state"><?php echo $address['state']; ?></span>
						<span class="info-item" data-key="country"><?php echo $address['country']; ?></span>
						<span class="info-item" data-key="zip"><?php echo $address['zip']; ?></span>
					</span>
					<?php endif; ?>
					<span class="vendor-address info-item edit-item">
						<input class="info-item" type="text" data-key="line1" name="vendorinfo[line1]" placeholder="<?php _e( 'Address 1', 'edd_fes' ); ?>" value="<?php echo $address['line1']; ?>" />
						<input class="info-item" type="text" data-key="line2" name="vendorinfo[line2]" placeholder="<?php _e( 'Address 2', 'edd_fes' ); ?>" value="<?php echo $address['line2']; ?>" />
						<input class="info-item" type="text" data-key="city" name="vendorinfo[city]" placeholder="<?php _e( 'City', 'edd_fes' ); ?>" value="<?php echo $address['city']; ?>" />
						<select data-key="country" name="vendorinfo[country]" id="billing_country" class="billing_country edd-select edit-item">
							<?php

							$selected_country = $address['country'];

							$countries = edd_get_country_list();
							foreach ( $countries as $country_code => $country ) {
								echo '<option value="' . esc_attr( $country_code ) . '"' . selected( $country_code, $selected_country, false ) . '>' . $country . '</option>';
							} ?>
						</select>
						<?php
							$selected_state = edd_get_shop_state();
							$states         = edd_get_shop_states( $selected_country );

							$selected_state = isset( $address['state'] ) ? $address['state'] : $selected_state;

							if ( ! empty( $states ) ) : ?>
								<select data-key="state" name="vendorinfo[state]" id="card_state" class="card_state edd-select info-item">
							<?php
								foreach ( $states as $state_code => $state ) {
									echo '<option value="' . $state_code . '"' . selected( $state_code, $selected_state, false ) . '>' . $state . '</option>';
								}
						?>
						</select>
						<?php else : ?>
						<input type="text" size="6" data-key="state" name="vendorinfo[state]" id="card_state" class="card_state edd-input info-item" placeholder="<?php _e( 'State / Province', 'edd_fes' ); ?>"/>
						<?php endif; ?>
						<input class="info-item" type="text" data-key="zip" name="vendorinfo[zip]" placeholder="<?php _e( 'Postal', 'edd_fes' ); ?>" value="<?php echo $address['zip']; ?>" />
					</span>
				<?php endif; ?>
				</div>

				<div class="vendor-main-wrapper left">

					<span class="vendor-name info-item edit-item"><input size="15" data-key="name" name="vendorinfo[name]" type="text" value="<?php echo $vendor->name; ?>" placeholder="<?php _e( 'Vendor Name', 'edd_fes' ); ?>" /></span>
					<span class="vendor-name info-item editable"><span data-key="name"><?php echo $vendor->name; ?></span></span>
					<span class="vendor-name info-item edit-item"><input size="20" data-key="email" name="vendorinfo[email]" type="text" value="<?php echo $vendor->email; ?>" placeholder="<?php _e( 'Vendor Email', 'edd_fes' ); ?>" /></span>
					<span class="vendor-email info-item editable" data-key="email"><?php echo $vendor->email; ?></span>
					<span class="vendor-since info-item">
						<?php printf( _x( '%s Since', 'FES uppercase singular setting for vendor', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = true ) ); ?>
						<?php echo date_i18n( get_option( 'date_format' ), strtotime( $vendor->date_created ) ) ?>
					</span>
					<span class="vendor-user-id info-item edit-item">
						<?php

							$user_id    = $vendor->user_id > 0 ? $vendor->user_id : '';
							$data_atts  = array( 'key' => 'user_login', 'exclude' => $user_id );
							$user_args  = array(
								'name'  => 'vendorinfo[user_login]',
								'class' => 'edd-user-dropdown',
								'data'  => $data_atts,
							);

							if ( ! empty( $user_id ) ) {
								$userdata = get_userdata( $user_id );
								$user_args['value'] = $userdata->user_login;
							}

							echo EDD()->html->ajax_user_search( $user_args );
						?>
						<input type="hidden" name="vendorinfo[user_id]" data-key="user_id" value="<?php echo $vendor->user_id; ?>" />
					</span>

					<span class="vendor-user-id info-item editable">
						<?php _e( 'User ID', 'edd_fes' ); ?>:&nbsp;
						<?php if ( intval( $vendor->user_id ) > 0 ) : ?>
							<span data-key="user_id"><?php echo $vendor->user_id; ?></span>
						<?php else : ?>
							<span data-key="user_id"><?php _e( 'none', 'edd_fes' ); ?></span>
						<?php endif; ?>
					</span>

					<span class="vendor-status info-item">
						<?php _e( 'Status', 'edd_fes' ); ?>:&nbsp;
						<?php
						$data = __( 'Unknown Status', 'edd_fes' );
						if ( $vendor->status == 'pending' ) {
							$data = __( 'Pending', 'edd_fes' );
						} else if ( $vendor->status == 'approved' ) {
							$data = __( 'Approved', 'edd_fes' );
						} else if ( $vendor->status == 'suspended' ) {
							$data =  __( 'Suspended', 'edd_fes' );
						} else {
							$data = __( 'Unknown Column', 'edd_fes' );
						}
						echo $data;
						?>
					</span>
					<span class="info-item vendor-store-link"><a title="<?php printf( _x( 'View %s store', 'FES uppercase singular setting for vendor', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = false ) ); ?>" href="<?php echo EDD_FES()->vendors->get_vendor_store_url( $vendor->user_id ); ?>" id="view-vendor-store"><?php printf( _x( 'View %s store', 'FES uppercase singular setting for vendor', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = false ) ) ?></a></span>
				</div>

			</div>

			<span id="vendor-edit-actions" class="edit-item">
				<input type="hidden" data-key="id" name="vendorinfo[id]" value="<?php echo $vendor->id; ?>" />
				<?php wp_nonce_field( 'edit-vendor', '_wpnonce', false, true ); ?>
				<input type="hidden" name="edd_action" value="edit-vendor" />
				<input type="submit" id="edd-edit-vendor-save" class="button-secondary" value="<?php printf( _x( 'Update %s', 'FES uppercase singular setting for vendor', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = true ) ); ?>" />
				<a id="edd-edit-vendor-cancel" href="" class="delete"><?php _e( 'Cancel', 'edd_fes' ); ?></a>
			</span>

		</form>
	</div>

	<?php 
	/**
	 * Admin Vendor Before Stats.
	 *
	 * Output right before the vendor
	 * stats on the admin vendor profile
	 * home tab.
	 *
	 * @since 2.3.0
	 *
	 * @param  FES_Vendor $vendor FES vendor being viewed.
	 */
	do_action( 'fes_vendor_before_stats', $vendor ); ?>

	<div id="vendor-stats-wrapper" class="vendor-section">
		<ul>
			<li>
				<?php if ( EDD_FES()->integrations->is_commissions_active() ) { ?>
				<a title="<?php _e( 'View All Purchases', 'edd_fes' ); ?>" href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-commissions&user=&user=' . urlencode( $vendor->user_id ) ); ?>">
				<?php } ?>
					<span class="dashicons dashicons-cart"></span>
					<?php printf( _n( '%d Sale To Date', '%d Sales To Date', $vendor->sales_count, 'edd_fes' ), $vendor->sales_count ); ?>
				<?php if ( EDD_FES()->integrations->is_commissions_active() ) { ?>
				</a>
				<?php } ?>
			</li>
			<li>
				<span class="dashicons dashicons-chart-area"></span>
				<?php echo edd_currency_filter( edd_format_amount( $vendor->sales_value ) ); ?> <?php _e( ' in Sales To Date', 'edd_fes' ); ?>
			</li>
			<?php
			/**
			 * Admin Vendor Stats List.
			 *
			 * Output next to the vendor
			 * stats on the admin vendor profile
			 * home tab.
			 *
			 * @since 2.3.0
			 *
			 * @param  FES_Vendor $vendor FES vendor being viewed.
			 */
			 do_action( 'fes_vendor_stats_list', $vendor ); ?>
		</ul>
	</div>

	<?php 
	/**
	 * Admin Vendor Before Tables.
	 *
	 * Output right before the vendor
	 * product & commission tables on the 
	 * admin vendor profile home tab.
	 *
	 * @since 2.3.0
	 *
	 * @param  FES_Vendor $vendor FES vendor being viewed.
	 */
	do_action( 'fes_vendor_before_tables_wrapper', $vendor ); ?>

	<div id="vendor-tables-wrapper" class="vendor-section">

		<?php do_action( 'fes_vendor_before_tables', $vendor ); ?>
		<?php if ( EDD_FES()->integrations->is_commissions_active() ) { ?>

			<h3><?php _e( 'Recently Earned Unpaid Commissions', 'edd_fes' ); ?></h3>
			<?php $unpaid_commissions = eddc_get_unpaid_commissions( array( 'user_id' => $vendor->user_id, 'number' => 5 ) ); ?>
			<table class="wp-list-table widefat striped payments">
				<thead>
					<tr>
						<th><?php _e( 'Item', 'edd_fes' ); ?></th>
						<th><?php _e( 'Amount', 'edd_fes' ); ?></th>
						<th><?php _e( 'Rate', 'edd_fes' ); ?></th>
						<th><?php _e( 'Date', 'edd_fes' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( ! empty( $unpaid_commissions ) ) : ?>
						<?php foreach ( $unpaid_commissions as $commission ) : ?>
							<tr>
								<?php
								$item_name        = get_the_title( get_post_meta( $commission->ID, '_download_id', true ) );
								$commission_info  = get_post_meta( $commission->ID, '_edd_commission_info', true );
								$amount           = $commission_info['amount'];
								$rate             = $commission_info['rate']; ?>
								<td><?php echo esc_html( $item_name ); ?></td>
								<td><?php echo edd_currency_filter( edd_format_amount( edd_sanitize_amount( $amount ) ) ); ?></td>
								<td><?php echo $rate . '%'; ?></td>
								<td><?php echo date_i18n( get_option( 'date_format' ), strtotime( $commission->post_date ) ); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr>
							<td colspan="4"><?php _e( 'No unpaid commissions', 'edd_fes' ); ?></td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>

		<?php } ?>

		<h3><?php printf( __( '%s', 'edd_fes' ), EDD_FES()->helper->get_product_constant_name( $plural = true, $uppercase = true ) ); ?></h3>
		<?php
		$products = wp_list_pluck( EDD_FES()->vendors->get_products( $user_id, 'publish' ), 'ID' ); ?>
		<table class="wp-list-table widefat striped downloads">
			<thead>
				<tr>
					<th><?php echo edd_get_label_singular(); ?></th>
					<th><?php _e( 'Sales', 'edd_fes' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( ! empty( $products ) ) : ?>
					<?php foreach ( $products as $product ) : if ( ! get_post( $product ) ) continue; ?>
						<tr>
							<td>
								<a title="<?php echo esc_attr( sprintf( _x( 'View %s', 'download title', 'edd_fes' ), get_the_title( $product ) ) ); ?>" href="<?php echo esc_url( admin_url( 'post.php?action=edit&post=' . $product ) ); ?>">
									<?php echo get_the_title( $product ); ?>
								</a>
							</td>
							<td><?php echo edd_get_download_sales_stats( $product ); ?></td>
						</tr>
					<?php endforeach; ?>
				<?php else : ?>
					<tr>
						<td colspan="4"><?php printf( _x( 'No Live %s', 'FES uppercase plural setting for download', 'edd_fes' ), EDD_FES()->helper->get_product_constant_name( $plural = true, $uppercase = true ) ); ?></td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>

		<?php 
		/**
		 * Admin Vendor After Tables.
		 *
		 * Output right after the vendor
		 * product & commission tables on the 
		 * admin vendor profile home tab.
		 *
		 * @since 2.3.0
		 *
		 * @param  FES_Vendor $vendor FES vendor being viewed.
		 */
		do_action( 'fes_vendor_after_tables', $vendor ); ?>

	</div>

	<?php 
	/**
	 * Admin Vendor Vendor Card Bottom.
	 *
	 * Output at the bottom of the home
	 * page of the admin vendor profile.
	 *
	 * @since 2.3.0
	 *
	 * @param  FES_Vendor $vendor FES vendor being viewed.
	 */
	do_action( 'fes_vendor_card_bottom', $vendor ); ?>

	<?php
}

/**
 * View the notes of a vendor.
 *
 * Renders the vendor notes view.
 *
 * @since 2.3.0
 * @access public
 * 
 * @param FES_Vendor $vendor The Vendor being displayed.
 * @return void
 */
function fes_vendor_notes_view( $vendor ) {

	$paged       = isset( $_GET['paged'] ) && is_numeric( $_GET['paged'] ) ? $_GET['paged'] : 1;
	$paged       = absint( $paged );
	$note_count  = $vendor->get_notes_count();
	$default     = 20;
	/**
	 * Admin Vendor Notes Per Page.
	 *
	 * Alters the number of vendor notes
	 * that appear per page.
	 *
	 * @since 2.3.0
	 *
	 * @param  int $default Number of vendor notes per page.
	 *                      Default 20.
	 */
	$per_page    = apply_filters( 'fes_vendor_notes_per_page', $default );
	$total_pages = ceil( $note_count / $per_page );

	$vendor_notes = $vendor->get_notes( $per_page, $paged ); ?>

	<div id="vendor-notes-wrapper">
		<div class="vendor-notes-header">
			<?php echo get_avatar( $vendor->email, 30 ); ?> <span><?php echo $vendor->name; ?></span>
		</div>
		<h3><?php _e( 'Notes', 'edd_fes' ); ?></h3>

		<?php if ( 1 == $paged ) : ?>
		<div style="display: block; margin-bottom: 35px;">
			<form id="edd-add-vendor-note" method="post" action="<?php echo admin_url( 'admin.php?page=fes-vendors&view=notes&id=' . $vendor->id ); ?>">
				<textarea id="vendor-note" name="vendor_note" class="vendor-note-input" rows="10"></textarea>
				<br />
				<input type="hidden" id="vendor-id" name="vendor_id" value="<?php echo $vendor->id; ?>" />
				<input type="hidden" name="edd_action" value="add-vendor-note" />
				<?php wp_nonce_field( 'add-vendor-note', 'add_vendor_note_nonce', true, true ); ?>
				<input id="add-vendor-note" class="right button-primary" type="submit" value="Add Note" />
			</form>
		</div>
		<?php endif; ?>

		<?php
		$pagination_args = array(
			'base'     => '%_%',
			'format'   => '?paged=%#%',
			'total'    => $total_pages,
			'current'  => $paged,
			'show_all' => true
		);

		echo paginate_links( $pagination_args );
		?>

		<div id="edd-vendor-notes">
		<?php if ( count( $vendor_notes ) > 0 ) : ?>
			<?php foreach ( $vendor_notes as $key => $note ) : ?>
				<div class="vendor-note-wrapper dashboard-comment-wrap comment-item">
					<span class="note-content-wrap">
						<?php echo stripslashes( $note ); ?>
					</span>
				</div>
			<?php endforeach; ?>
		<?php else: ?>
			<div class="edd-no-vendor-notes">
				<?php _e( 'No Vendor Notes', 'edd_fes' ); ?>
			</div>
		<?php endif; ?>
		</div>

		<?php echo paginate_links( $pagination_args ); ?>

	</div>

	<?php
}


/**
 * View the registration of a vendor.
 *
 * Outputs the FES vendor profile form
 * as a tab of the admin vendor profile.
 *
 * @since  2.3
 * @access public
 * 
 * @param FES_Vendor $vendor The vendor being displayed.
 * @return void
 */
function fes_vendor_registration_view( $vendor ) { ?>
	<div id="vendor-registration-wrapper">
		<div class="vendor-registration-header">
			<?php echo get_avatar( $vendor->email, 30 ); ?> <span><?php echo $vendor->name; ?></span>
		</div>
		<h3><?php _e( 'Registration Data', 'edd_fes' ); ?></h3>
		<?php
		$user_id = get_current_user_id();
		$form_id = EDD_FES()->helper->get_option( 'fes-registration-form', false );
		$readonly = false;
		EDD_FES()->setup->enqueue_form_assets();
		$output = '';

		// Make the FES Form
		$form = EDD_FES()->helper->get_form_by_id( $form_id, $vendor->user_id );

		// Render the FES Form
		$output .= $form->render_form_admin( $user_id, $readonly );
		echo $output;
		?>
	</div>
	<?php
}

/**
 * View the profile of a vendor.
 *
 * Outputs the vendor profile form
 * as a tab on the admin vendor profile.
 *
 * @since 2.3.0
 * @access public
 * 
 * @param FES_Vendor $vendor The vendor being displayed.
 * @return void
 */
function fes_vendor_profile_view( $vendor ) { ?>
	<div id="vendor-profile-wrapper">
		<div class="vendor-profile-header">
			<?php echo get_avatar( $vendor->email, 30 ); ?> <span><?php echo $vendor->name; ?></span>
		</div>
		<h3><?php _e( 'Profile Data', 'edd_fes' ); ?></h3>
		<?php
		$user_id = get_current_user_id();
		$form_id = EDD_FES()->helper->get_option( 'fes-profile-form', false );
		$readonly = false;
		EDD_FES()->setup->enqueue_form_assets();
		$output = '';

		// Make the FES Form
		$form = EDD_FES()->helper->get_form_by_id( $form_id, $vendor->user_id );

		// Render the FES Form
		$output .= $form->render_form_admin( $user_id, $readonly );
		echo $output;
		?>
	</div>
	<?php
}

/**
 * View the products of a vendor.
 *
 * Outputs a list of the vendor
 * products as a tab on the admin
 * vendor profile.
 *
 * @since 2.3.0
 * @access public
 * 
 * @param FES_Vendor $vendor The vendor being displayed
 * @return void
 */
function fes_vendor_products_view( $vendor ) { ?>
	<div id="vendor-products-wrapper">
		<div class="vendor-products-header">
			<?php echo get_avatar( $vendor->email, 30 ); ?> <span><?php echo $vendor->name; ?></span>
		</div>
		<h3><?php echo EDD_FES()->helper->get_product_constant_name( $plural = true, $uppercase = true ); ?></h3>
		<?php

			$concat = get_option( "permalink_structure" ) ? "?" : "&";
			$products = EDD_FES()->vendors->get_all_products( $vendor->user_id );
			$sales = 0;
			$earnings = 0;

			if ( !empty( $products ) ) { ?>
					<table class="widefat" id="fes-all-products">
						<thead>
							 <tr>
								<th><?php _e( 'ID', 'edd_fes' ); ?></th>
								<th><?php _e( 'Title', 'edd_fes' ); ?></th>
								<th><?php _e( 'Status', 'edd_fes' ); ?></th>
								<th><?php _e( 'Sales', 'edd_fes' ); ?></th>
							</tr>
						</thead>
						 <tbody>

						 <?php 
							$i = 'alternate';
							foreach ( $products as $product ) : 
							?>
							 <tr <?php if ( $i == 'alternate' ) { echo 'class="alternate"'; } ?>>
								<td><?php echo esc_html( $product['ID'] ); ?></td>
								<td><a href="<?php echo esc_html( $product['url'] ); ?>"><?php echo esc_html( $product['title'] ); ?></a></td>
								<td><?php echo esc_html( $product['status'] ); ?></td>
								<td><?php echo esc_html( $product['sales'] ); ?></td>
							</tr>
						<?php
						$sales+= $product['sales']; 
							  $i = $i == 'alternate' ? 'regular' : 'alternate'; ?>
						<?php endforeach; ?>
							<tr>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
							</tr>
						</tbody>
						<tfoot>
							<td><strong><?php _e( 'Total Sales', 'edd_fes' ); ?></strong></td>
								<td></td>
								<td></td>
								<td><?php echo $sales; ?></td>
						</tfoot>
					</table>
				<?php
			} else {
				printf( __( '%s has no %s', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = true ), EDD_FES()->helper->get_product_constant_name( $plural = true, $uppercase = false ) );
			}
			?>
		</div>
	</div>
	<?php
}

/**
 * View the commissions of a vendor.
 *
 * Shows the commissions tab content
 * on the admin vendor profile.
 *
 * @since 2.3.0
 * @access public
 * 
 * @param FES_Vendor $vendor The vendor being displayed.
 * @return void
 */
function fes_vendor_commissions_view( $vendor ) { ?>
	<div id="vendor-commissions-wrapper">
		<div class="vendor-commissions-header">
			<?php echo get_avatar( $vendor->email, 30 ); ?> <span><?php echo $vendor->name; ?></span>
		</div>
		<?php
			$user_id = $vendor->user_id;

			// If still empty, exit
			if ( empty( $user_id ) ) {
				return;
			}
			?>
			<div id="edd_user_commissions">
				<?php
					echo eddc_user_commissions_overview( $user_id );
					echo eddc_user_commissions( $user_id );
				?>
			</div>
	</div>
	<?php
}

/**
 * View the reports of a vendor.
 *
 * For now simply outputs a graph of
 * earnings, sales and commissions.
 *
 * @since 2.3.0
 * @access public
 *
 * @todo Add more reports.
 * 
 * @param FES_Vendor $vendor The vendor being displayed.
 * @return void
 */
function fes_vendor_reports_view( $vendor ) { ?>
	<div id="vendor-reports-wrapper">
		<div class="vendor-reports-header">
			<?php echo get_avatar( $vendor->email, 30 ); ?> <span><?php echo $vendor->name; ?></span>
		</div>
		<h3><?php _e( 'Reports', 'edd_fes' ); ?></h3>
		<?php echo fes_reports_graph( $vendor ); ?>
	</div>
	<?php
}

/**
 * View the exports of a vendor.
 *
 * For now this allows an admin
 * to export a CSV of a vendor's customers.
 *
 * @since 2.3.0
 * @access public
 *
 * @todo  Add more exports.
 * 
 * @param FES_Vendor $vendor The vendor being displayed.
 * @return void
 */
function fes_vendor_exports_view( $vendor ) { 
	$products = EDD_FES()->vendors->get_all_products( $vendor->user_id );
	$arr = array();
	if ( empty ( $products ) ){
		?>
		<div id="vendor-exports-wrapper">
			<div class="vendor-exports-header">
				<?php echo get_avatar( $vendor->email, 30 ); ?> <span><?php echo $vendor->name; ?></span>
			</div>
			<h3><?php _e( 'Exports', 'edd_fes' ); ?></h3>
			<div id="post-body-content">
				<?php _e( 'Nothing to export!', 'edd_fes' ); ?>
			</div>
		</div>
		<?php
		return;
	}
	foreach( $products as $product ){
		$arr[] = $product['ID'];
	}

	?>
	<div id="vendor-exports-wrapper">
		<div class="vendor-exports-header">
			<?php echo get_avatar( $vendor->email, 30 ); ?> <span><?php echo $vendor->name; ?></span>
		</div>
		<h3><?php _e( 'Exports', 'edd_fes' ); ?></h3>
		<div id="post-body-content">

			<?php do_action( 'fes_reports_tab_export_content_top' ); ?>

			<div class="postbox edd-export-pdf-sales-earnings">
				<div class="inside">
					<h4><?php _e( 'Export PDF of Sales and Earnings', 'edd_fes' ); ?></h4>
					<p><?php echo sprintf( _x( 'Download a PDF of Sales and Earnings reports for all %s for the current year.', 'FES uppercase plural setting for download', 'edd_fes' ), EDD_FES()->helper->get_product_constant_name( $plural = true, $uppercase = false ) ); ?></p>
					<p><a class="button" href="<?php echo wp_nonce_url( add_query_arg( array( 'edd-action' => 'generate_fes_pdf' ) ), 'edd_generate_fes_pdf' ); ?>"><?php _e( 'Generate PDF', 'edd_fes' ); ?></a></p>
				</div>
			</div>

			<div class="postbox edd-export-customers">
				<div class="inside">
				<h4><?php _e('Export Customers in CSV', 'edd_fes'); ?></h4>
					<p><?php echo sprintf( _x( 'Download a CSV of all customer emails. Optionally export only customers that have purchased a particular %s. Note, if you have a large number of customers, exporting the purchase stats may fail.', 'FES lowercase singular setting for download', 'edd_fes' ), EDD_FES()->helper->get_product_constant_name( $plural = false, $uppercase = false ) ); ?></p>
					<p>
						<form method="post" id="edd_customer_export">
							<select name="edd_export_download" id="edd_customer_export_download">
								<option value="0"><?php printf( _x( 'All %s', 'FES uppercase plural setting for vendor', 'edd_fes' ), EDD_FES()->helper->get_product_constant_name( $plural = true, $uppercase = true ) ); ?></option>
								<?php
								$downloads = get_posts( array( 'post_type' => 'download', 'posts_per_page' => -1, 'post__in' => $arr ) );
								if ( $downloads ) {
									foreach( $downloads as $download ) {
										echo '<option value="' . $download->ID . '">' . get_the_title( $download->ID ) . '</option>';
									}
								}
								?>
							</select>
							<select name="fes_export_option" id="fes_export_option">
								<option value="emails"><?php _e( 'Emails', 'edd_fes' ); ?></option>
								<option value="emails_and_names"><?php _e( 'Emails and Names', 'edd_fes' ); ?></option>
								<!--<option value="full"><?php //_e( 'Emails, Names, and Purchase Stats', 'edd_fes' ); ?></option>-->
							</select>
							<input type="hidden" name="edd-action" value="email_fes_export"/>
							<input type="submit" value="<?php _e( 'Generate CSV', 'edd_fes' ); ?>" class="button-secondary"/>
						</form>
					</p>
				</div>
			</div>
		</div>
	</div>
	<?php
}

/**
 * FES export vendor customers.
 *
 * This exports a CSV of a vendor's customers.
 *
 * @since 2.3.0
 * @access public
 * 
 * @return void
 */
function fes_export_all_customers() {
	$customer_export = new FES_Customers_Export();
	$customer_export->export();
}
add_action( 'edd_email_fes_export', 'fes_export_all_customers' );

/**
 * FES vendor table bulk action.
 *
 * Used for a bulk status change.
 *
 * @since 2.3.0
 * @access public
 * 
 * @return void
 */
function fes_vendor_table_process_bulk_action() {
	$ids = isset( $_GET['vendor'] ) ? $_GET['vendor'] : false;

	if ( empty( $ids ) ){
		return;
	}

	if ( !is_array( $ids ) ){
		$ids = array( $ids );
	}

	$current_action = $_GET['action'];

	foreach ( $ids as $id ) {
		if ( $id < 2 ) {
			continue;
		}

		fes_change_vendor_status( $id, $current_action, false );
	}
}
add_action( 'admin_init', 'fes_vendor_table_process_bulk_action' );

/**
 * FES change vendor status.
 *
 * Used for a vendor status change action button.
 *
 * @since 2.3.0
 * @access public
 *
 * @param int $id Id of the user whose vendor account status is being changed.
 * @param string $status Status to change vendor to.
 * @param bool $output Whether to output a JSON encoded array with message.
 * @return mixed If output is requested, a JSON encoded array with message.
 */
function fes_change_vendor_status( $id = 0, $status = '', $output = true ) {
	if ( fes_is_ajax_request() ){
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		if ( isset( $_REQUEST[ 'vendor' ] ) ) {
			$id = intval( $_REQUEST[ 'vendor' ] );
		}

		if ( isset( $_REQUEST[ 'status' ] ) ) {
			$status = sanitize_text_field( $_REQUEST[ 'status' ] );
		}

		if ( isset( $_REQUEST[ 'output' ] ) ) {
			$output = (bool) $_REQUEST[ 'output' ];
		}		
	}


	if ( !EDD_FES()->vendors->user_is_admin() ){
		if ( $output ){
			$output = array();
			$output['title'] = __( 'Error!', 'edd_fes' );
			$output['message'] = __( 'You don\'t have the permissions to do that!', 'edd_fes' );
			$output['redirect_to'] = '#';
			$output['success'] = false;
			if ( fes_is_ajax_request() ){
				echo json_encode( $output );
				exit;
			} else {
				return $output;
			}
		} else {
			return;
		}
	}

	$vendor  = new FES_Vendor( $id, true );
	$output = $vendor->change_status( $status, true, $output);
	if ( $output ){
		if ( fes_is_ajax_request() ){
			echo json_encode( $output );
			exit;
		} else {
			return $output;
		}
	} else {
		return;
	}
}
add_action( 'wp_ajax_fes_change_vendor_status', 'fes_change_vendor_status', 10, 3 );

/**
 * FES vendor row links.
 *
 * If the user is a vendor, then
 * offer a link to the vendor profile 
 * page on the WordPres core user table.
 *
 * @since 2.3.0
 * @access public
 *
 * @param  array $actions Actions (links) for admins to perform on a user.
 * @param  WP_User $user_object User object of the user for a row.
 * @return array Actions for the admin.
 */
function vendor_row_links( $actions, $user_object ) {
	$is_pending   = EDD_FES()->vendors->user_is_status( 'pending', $user_object->ID );
	$is_vendor    = EDD_FES()->vendors->user_is_status( 'approved', $user_object->ID );
	$is_suspended = EDD_FES()->vendors->user_is_status( 'suspended', $user_object->ID );
	if ( $is_suspended || $is_pending || $is_vendor ) {
		$vendor  = new FES_Vendor( $user_object->ID, true );
		$id = $vendor->id;
		$actions['to_vendor_page'] = "<a href='" . admin_url( "admin.php?page=fes-vendors&view=overview&id=$id") . "'>" . sprintf( _x( 'View %s Profile', 'FES uppercase singular setting for vendor','edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = true ) ) . "</a>";
	}
	return $actions;
}
add_action( 'user_row_actions', 'vendor_row_links', 10, 2 );

/**
 * FES replace column.
 *
 * Adds a column for vendor status to the 
 * user list table.
 *
 * @since 2.3.0
 * @access public
 *
 * @param  array $columns Columns on the user list table.
 * @return array Columns on the user list table.
 */
function fes_replace_column( $columns ) {
	unset( $columns['posts'] );
	$columns['fes_vendor'] = sprintf( _x( '%s Status', 'FES uppercase singular setting for vendor', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = true ) );
	$columns['posts']      = __( 'Posts' );
	return $columns;
}

/**
 * FES user column content.
 *
 * Outputs the content for the vendor
 * status column FES adds to the user
 * list table.
 *
 * @since 2.3.0
 * @access public
 * 
 * @param  string $output Content for a column.
 * @param  string $column Column currently being filtered.
 * @param  int    $user_id User ID of the current row.
 * @return string Content for the column in the user list table.
 */
function fes_output_user_column_content( $output, $column, $user_id ) {
	if ( $column !== 'fes_vendor' ){
		return $output;
	}

	$is_pending   = EDD_FES()->vendors->user_is_status( 'pending', $user_id );
	$is_vendor    = EDD_FES()->vendors->user_is_status( 'approved', $user_id );
	$is_suspended = EDD_FES()->vendors->user_is_status( 'suspended', $user_id );
	$role = '';
	if ( $is_suspended ){
		$vendor  = new FES_Vendor( $user_id, true );
		$id = $vendor->id;
		$role = '<a href="' . admin_url( "admin.php?page=fes-vendors&view=overview&id=$id" ) . '" >' . __( 'Suspended', 'edd_fes' ) . '</a>';
	} else if ( $is_pending ){
		$vendor  = new FES_Vendor( $user_id, true );
		$id = $vendor->id;
		$role = '<a href="' . admin_url( "admin.php?page=fes-vendors&view=overview&id=$id" ) . '" >' . __( 'Pending', 'edd_fes' ) . '</a>';
	} else if ( $is_vendor ){
		$vendor  = new FES_Vendor( $user_id, true );
		$id = $vendor->id;
		$role = '<a href="' . admin_url( "admin.php?page=fes-vendors&view=overview&id=$id" ) . '" >' . __( 'Approved', 'edd_fes' ) . '</a>';
	} else {
		$role =  sprintf( _x( 'Not a %s', 'FES lowercase singular setting for vendor', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = false ) );
	}
	return $role;
}

add_filter( 'manage_users_columns',       'fes_replace_column' , 11 );
add_filter( 'manage_users_custom_column', 'fes_output_user_column_content' , 10, 3 );

/**
 * FES user profile metabox content.
 *
 * Outputs a metabox on the my profile
 * and profile page for a user in WordPress
 * offering FES vendor status and actions.
 *
 * @since 2.3.0
 * @access public
 * 
 * @param  WP_User $user User object of the user currently
 *                       being edited.
 * @return void
 */
function fes_user_edit_profile_metabox( $user ) {
	?>
	<h3><?php _e('Easy Digital Downloads Frontend Submissions', 'edd_fes'); ?></h3>
	<table class="form-table">
		<tr>
			<th><label><?php _e('Status:', 'edd_fes'); ?></label></th>
			<td>
				<?php 
				$db_user = new FES_DB_Vendors();
				$output = '';
				if ( !$db_user->exists( 'user_id', $user->ID ) ){
					$output = __( 'Not a vendor', 'edd_fes' );
				} else{
					$vendor = $db_user->get_vendor_by( 'user_id', $user->ID );
					if ( $vendor->status == 'pending' ){
						$output = __( 'Pending', 'edd_fes' );
					} else if ( $vendor->status == 'approved' ){
						$output = __( 'Approved', 'edd_fes' );
					} else if ( $vendor->status == 'suspended' ){
						$output = __( 'Suspended', 'edd_fes' );
					} else {
						$output = __( 'Unknown', 'edd_fes' );
					}
					$id = $vendor->id;
					$output .= "<br /><a href='" . admin_url( "admin.php?page=fes-vendors&view=overview&id=$id") . "'>" . sprintf( _x( 'View %s Profile', 'FES uppercase singular setting for vendor','edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = true ) ) . "</a>";
				}
				?>
				<span><?php echo $output; ?></span>
			</td>
		</tr>
		<?php 
		$db_user = new FES_DB_Vendors();
		if ( EDD_FES()->vendors->user_is_admin() && !$db_user->exists( 'user_id', $user->ID ) ) { ?>
		<tr>
			<th><label><?php _e('Actions:', 'edd_fes'); ?></label></th>
			<td>
				<?php 
				$output  = '';
				$link    = sprintf( _x( 'Make %s', 'FES lowercase singular setting for vendor', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = false ) );
				$string  =  __( 'Create', 'edd_fes' );
				$output .= sprintf( '<a class="create-vendor-user-edit" data-vendor="%d" data-status="approved" data-nstatus="%s" href="#"/>%s</a>', $user->ID, $string, $link );
				?>
				<span><?php echo $output; ?></span>
			</td>
		</tr>
		<?php } ?>
	</table>
	<?php
}
add_action( 'show_user_profile', 'fes_user_edit_profile_metabox', 2, 1 );
add_action( 'edit_user_profile', 'fes_user_edit_profile_metabox', 2, 1 );

/**
 * FES user profile change status.
 *
 * Change status action from the user profile or
 * my profile page in WordPress.
 *
 * @since 2.3.0
 * @access public
 *
 * @todo  How many of these types of functions do we have?
 *        We can probably simplify these down.
 * 
 * @param  int $id User id of the user currently
 *                       being edited.
 * @return array JSON encoded array with message. 
 */
function fes_create_vendor_user_edit( $id = 0 ) {
	if ( !fes_is_ajax_request() ){
		return;
	}

	@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
	
	if ( isset( $_REQUEST[ 'vendor' ] ) ) {
		$id = intval( $_REQUEST[ 'vendor' ] );
	}

	if ( !EDD_FES()->vendors->user_is_admin() ){
		$output = array();
		$output['title'] = __( 'Error!', 'edd_fes' );
		$output['message'] = __( 'You don\'t have the permissions to do that!', 'edd_fes' );
		$output['redirect_to'] = '#';
		$output['success'] = false;
		echo json_encode( $output );
		exit;
	}	

	$user = new WP_User( $id );
	if ( !$user || !is_object( $user ) ){
		$output = array();
		$output['title'] = __( 'Error!', 'edd_fes' );
		$output['message'] = __( 'Invalid user id!', 'edd_fes' );
		$output['redirect_to'] = '#';
		$output['success'] = false;
		echo json_encode( $output );
		exit;		
	}

	$db_user = new FES_DB_Vendors();
	if ( $db_user->exists( 'user_id', $user->ID ) ){
		$output = array();
		$output['title'] = __( 'Error!', 'edd_fes' );
		$output['message'] = __( 'Invalid action!', 'edd_fes' );
		$output['redirect_to'] = '#';
		$output['success'] = false;
		echo json_encode( $output );
		exit;
	}

	// create user
	$db_user->add( array(
		'user_id'        => $user->ID,
		'email'          => $user->user_email,
		'username'       => $user->user_login,
		'name'           => $user->display_name,
		'product_count'  => 0,
		'sales_count'	 => 0,
		'sales_value'	 => 0.00,
		'status'		 => 'pending',
		'notes'          => '',
		'date_created'   => date( 'Y-m-d H:i:s' ),
	) );
	
	// set to approved
	$vendor  = new FES_Vendor( $user->ID, true );
	$output = $vendor->change_status( 'approved', true, true );

	if ( isset( $output['success'] ) && $output['success'] === false ){
		$vendor_id = EDD_FES()->vendors->get_vendor_id( $user->user_login );
		$db_user->delete( $vendor_id );
	}
	echo json_encode( $output );
	exit;
}
add_action( 'wp_ajax_fes_create_vendor_user_edit', 'fes_create_vendor_user_edit', 10, 1 );

/**
 * FES admin submit profile form.
 *
 * Processes the submission of the profile form
 * in the WordPress admin.
 *
 * @since 2.3.0
 * @access public
 * 
 * @param  int $id User id of the user currently being edited.
 * @param  array $values Values to save.
 * @param  array $args Args for the save function. Deprecated.
 * @return void
 */
function fes_admin_submit_profile_form( $id = 0, $values = array(), $args = array() ) {
	$form_id   = !empty( $values ) && isset( $values['form_id'] )   ? absint( $values['form_id'] )     : ( isset( $_REQUEST['form_id'] )   ? absint( $_REQUEST['form_id'] )   : EDD_FES()->helper->get_option( 'fes-profile-form', false ) );
	$user_id   = !empty( $values ) && isset( $values['user_id'] )   ? absint( $values['user_id'] )     : ( isset( $_REQUEST['user_id'] )   ? absint( $_REQUEST['user_id'] )   : get_current_user_id() );
	$vendor_id = !empty( $values ) && isset( $values['vendor_id'] ) ? absint( $values['vendor_id'] )   : ( isset( $_REQUEST['vendor_id'] ) ? absint( $_REQUEST['vendor_id'] ) : -2 );
	$values    = !empty( $values ) ? $values : $_POST;

	// Make the FES Form
	$form      = new FES_Profile_Form( $form_id, 'id', $vendor_id );

	// Save the FES Form
	$form->save_form_admin( $values , $user_id );
}
add_action( 'wp_ajax_fes_submit_profile_form', 'fes_admin_submit_profile_form', 10, 3 );

/**
 * FES admin submit registration form.
 *
 * Processes the submission of the registration form
 * in the WordPress admin.
 *
 * @since 2.3.0
 * @access public
 * 
 * @param  int $id User id of the user currently being edited.
 * @param  array $values Values to save.
 * @param  array $args Args for the save function. Deprecated.
 * @return void
 */
function fes_admin_submit_registration_form( $id = 0, $values = array(), $args = array() ) {
	$form_id   = !empty( $values ) && isset( $values['form_id'] )   ? absint( $values['form_id'] )   : ( isset( $_REQUEST['form_id'] )   ? absint( $_REQUEST['form_id'] )   : EDD_FES()->helper->get_option( 'fes-login-form', false ) );
	$user_id   = !empty( $values ) && isset( $values['user_id'] )   ? absint( $values['user_id'] )   : ( isset( $_REQUEST['user_id'] )   ? absint( $_REQUEST['user_id'] )   : get_current_user_id() );
	$vendor_id = !empty( $values ) && isset( $values['vendor_id'] ) ? absint( $values['vendor_id'] ) : ( isset( $_REQUEST['vendor_id'] ) ? absint( $_REQUEST['vendor_id'] ) : -2 );

	$values    = !empty( $values ) ? $values : $_POST;
	// Make the FES Form
	$form      = new FES_Registration_Form( $form_id, 'id', $vendor_id );

	// Save the FES Form
	$form->save_form_admin( $values, $user_id );
}
add_action( 'wp_ajax_fes_submit_registration_form', 'fes_admin_submit_registration_form', 10, 3  );