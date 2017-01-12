<?php
/**
 * FES Tracking System
 *
 * This file deals with FES's optin tracking system.
 *
 * @package FES
 * @subpackage Tracking
 * @since 2.3.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { 
	exit;
}

/**
 * FES Usage Tracking.
 *
 * Tracking functions for reporting plugin usage 
 * to the FES site for users that have opted in.
 *
 * @since 2.3.0
 * @access public
 */
class FES_Tracking {

	/**
	 * The data to send to the FES site
	 *
	 * @since 2.3.0
	 * @access private
	 * @var array $data Data to send.
	 */		
	private $data;

	/**
	 * FES Tracking Actions.
	 *
	 * Runs actions required to send
	 * FES tracking data and show the
	 * optin notice.
	 *
	 * @since 2.3.0
	 * @access public
	 * 
	 * @return void
	 */
	public function __construct() {
		$this->schedule_send();

		add_action( 'edd_settings_general_sanitize', array( $this, 'check_for_settings_optin' ) );
		add_action( 'edd_fes_opt_into_tracking', array( $this, 'check_for_optin' ) );
		add_action( 'edd_fes_opt_out_of_tracking', array( $this, 'check_for_optout' ) );
		add_action( 'admin_notices', array( $this, 'admin_notice' ) );
	}

	/**
	 * FES Tracking Allowed.
	 *
	 * Check if the user has opted into tracking
	 *
	 * @since 2.3.0
	 * @access private
	 * 
	 * @return bool If the user has opted into tracking
	 */	
	private function tracking_allowed() {
		$allow_tracking = edd_get_option( 'fes_allow_tracking', false );
		return isset( $allow_tracking );
	}

	/**
	 * Setup tracking data.
	 *
	 * Setup the data that is going to be tracked
	 *
	 * @since 2.3.0
	 * @access private
	 * 
	 * @return void
	 */	
	private function setup_data() {
		global $fes_settings;
		$data = array();

		// Retrieve current theme info
		$theme_data = wp_get_theme();
		$theme      = $theme_data->Name . ' ' . $theme_data->Version;

		$data['url']    = home_url();
		$data['theme']  = $theme;
		$data['email']  = get_bloginfo( 'admin_email' );

		// Retrieve current plugin information
		if ( ! function_exists( 'get_plugins' ) ) {
			include ABSPATH . '/wp-admin/includes/plugin.php';
		}

		$plugins        = array_keys( get_plugins() );
		$active_plugins = get_option( 'active_plugins', array() );

		foreach ( $plugins as $key => $plugin ) {
			if ( in_array( $plugin, $active_plugins ) ) {
				// Remove active plugins from list so we can show active and inactive separately
				unset( $plugins[ $key ] );
			}
		}

		$data['active_plugins']     = $active_plugins;
		$data['inactive_plugins']   = $plugins;
		$data['products']           = wp_count_posts( 'download' )->publish;
		$data['submission_form']	    = get_post_meta( EDD_FES()->helper->get_option( 'fes-submission-form', false ), 'fes-form', true );
		$data['registration_form']    = get_post_meta( EDD_FES()->helper->get_option( 'fes-registration-form', false ), 'fes-form', true );
		$data['profile_form']        = get_post_meta( EDD_FES()->helper->get_option( 'fes-profile-form', false ), 'fes-form', true );
		$data['settings'] 	    =  $fes_settings;
		$data['fes_version']		= fes_plugin_version;
		$data['edd_version']		= EDD_VERSION;
		$data['fields']				= EDD_FES()->load_fields;
		$data['forms']				= EDD_FES()->load_forms;
		$db_vendors 				= new FES_DB_Vendors();
		$data['vendor_count']	    = $db_vendors->count();
		$stats 						= new EDD_API();
		$stats 						= $stats->get_stats();
		$stats 						= $stats['stats'];
		$data['vendor_stats']		= $stats;

		$this->data = $data;
	}

	/**
	 * Send tracking data.
	 *
	 * Send the data if tracking is allowed to
	 * the FES server.
	 *
	 * @since 2.3.0
	 * @access private
	 *
	 * @param bool $override Whether to override and return early.
	 * @return void
	 */		
	public function fes_send_checkin( $override = false ) {

		if ( ! $this->tracking_allowed() && ! $override ) {
			return;
		}

		// Send a maximum of once per week
		$last_send = $this->get_last_send();
		if ( $last_send && $last_send > strtotime( '-1 week' ) ) {
			return;
		}

		$this->setup_data();

		$request = wp_remote_post( 'http://www.fesproving.com/inbound.php', array(
			'method'      => 'POST',
			'timeout'     => 20,
			'redirection' => 5,
			'httpversion' => '1.1',
			'blocking'    => true,
			'body'        => $this->data,
			'user-agent'  => 'FES/' . fes_plugin_version . '; ' . get_bloginfo( 'url' )
		) );

		update_option( 'fes_tracking_last_send', time() );

	}

	/**
	 * Check for a new opt-in on settings save.
	 *
	 * This runs during the sanitation of General settings,
	 * thus the return.
	 *
	 * @since 2.3.0
	 * @access private
	 *
	 * @param  array $input EDD settings being saved.
	 * @return array EDD settings being saved.
	 */	
	public function check_for_settings_optin( $input ) {
		// Send an intial check in on settings save

		if ( isset( $input['fes_allow_tracking'] ) ) {
			$this->fes_send_checkin( true );
		}

		return $input;

	}

	/**
	 * FES admin notice optin.
	 *
	 * Check for a new opt-in via the admin notice.
	 *
	 * @since 2.3.0
	 * @access private
	 *
	 * @param  array $data Unused.
	 * @return void
	 */		
	public function check_for_optin( $data ) {

		edd_update_option( 'fes_allow_tracking', '1' );

		$this->fes_send_checkin( true );

		update_option( 'fes_tracking_notice', '1' );

	}

	/**
	 * FES admin notice optout.
	 *
	 * Check for a opt-out via the admin notice.
	 *
	 * @since 2.3.0
	 * @access private
	 *
	 * @param  array $data Unused.
	 * @return void
	 */	
	public function check_for_optout( $data ) {

		if ( edd_get_option( 'allow_tracking', false ) ) {
			edd_update_option( 'fes_allow_tracking', false );
		}

		update_option( 'fes_tracking_notice', '1' );

		wp_redirect( remove_query_arg( 'edd_action' ) ); exit;

	}

	/**
	 * Time of last checkin.
	 *
	 * Get the last time a checkin was sent.
	 *
	 * @since 2.3.0
	 * @access private
	 *
	 * @return false|string When the checkin was last sent.
	 */		
	private function get_last_send() {
		return get_option( 'fes_tracking_last_send' );
	}

	/**
	 * Schedule a weekly checkin.
	 *
	 * Adds the FES tracking send action
	 * to the regularly scheduled events.
	 *
	 * @since 2.3.0
	 * @access private
	 *
	 * @return void
	 */
	private function schedule_send() {
		// We send once a week (while tracking is allowed) to check in, which can be used to determine active sites
		add_action( 'edd_weekly_scheduled_events', array( $this, 'fes_send_checkin' ) );
	}

	/**
	 * Show tracking optin notice.
	 *
	 * Display the admin notice to users 
	 * that have not opted-in or out.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @return void
	 */	
	public function admin_notice() {
		$hide_notice = get_option( 'fes_tracking_notice' );

		if ( $hide_notice ) {
			return;
		}

		if ( edd_get_option( 'fes_allow_tracking', false ) ) {
			return;
		}

		if ( ! EDD_FES()->vendors->user_is_admin() ) {
			return;
		}

		if (
			stristr( network_site_url( '/' ), 'dev'       ) !== false ||
			stristr( network_site_url( '/' ), 'localhost' ) !== false ||
			stristr( network_site_url( '/' ), ':8888'     ) !== false // This is common with MAMP on OS X
		) {
			update_option( 'fes_tracking_notice', '1' );
		} else {
			$optin_url  = add_query_arg( 'edd_action', 'fes_opt_into_tracking' );
			$optout_url = add_query_arg( 'edd_action', 'fes_opt_out_of_tracking' );

			echo '<div class="updated"><p>';
				echo __( 'Allow EDD FES to tell Chris, the developer of FES, how you\'re using FES so he can work on making it better for everyone. No sensitive data is tracked.', 'edd_fes' );
				echo '</p>';
				echo '<p>';
				echo '&nbsp;<a href="' . esc_url( $optin_url ) . '" class="button-secondary">' . __( 'Absolutely, I\'ll help the cause', 'edd_fes' ) . '</a>';
				echo '&nbsp;<a href="' . esc_url( $optout_url ) . '" class="button-secondary">' . __( 'Do not allow', 'edd_fes' ) . '</a>';
				echo '</p>';
				echo '<p>';
				echo sprintf( _x( 'You can read a full list of the data collected (and why I need it) %s here %s', 'tracking link is the %s', 'edd_fes' ), '<a href="http://docs.easydigitaldownloads.com/article/697-tracking-in-frontend-submissions" target="_blank">', '</a>');
				echo '</p>';
			echo '</div>';
		}
	}

}
$fes_tracking = new FES_Tracking;
