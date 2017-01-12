<?php
/**
 * FES Setup
 *
 * This file contains code that needs to run
 * before most other things in FES
 *
 * @package FES
 * @subpackage Setup
 * @since 2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { 
	exit;
}

/**
 * FES Setup.
 *
 * This file contains code that needs to run
 * before most other things in FES
 *
 * @since 2.0.0
 * @access public
 */
class FES_Setup {

	/**
	 * FES Setup action/filters.
	 *
	 * Registers the actions and filters.
	 *
	 * @since 2.3.0
	 * @access public
	 * 
	 * @return void
	 */	
	public function __construct() {
		add_action( 'switch_theme', 	     'flush_rewrite_rules', 15 );
		
		add_action( 'wp_enqueue_scripts',	 array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_enqueue_scripts',	 array( $this,	'enqueue_styles'  ) );
		add_action( 'admin_enqueue_scripts', array(	$this, 'admin_enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ) );
		add_action( 'wp_head', 				 array( $this, 'fes_version' ) );
		add_action( 'admin_head', 			 array( $this, 'admin_head' ) );
		add_filter( 'media_upload_tabs', 	 array( $this, 'remove_media_library_tab' ) );
		add_filter( 'ajax_query_attachments_args', array( $this, 'restrict_media' ) );
		add_action( 'admin_notices', 		 array( $this, 'no_vendor_dashboard_set' ) );
		add_action( 'admin_notices', array( $this, 'test_if_editors_installed' ) );

		add_post_type_support( 'download', 	 'author' );
		add_post_type_support( 'download', 	 'comments' );
		add_post_type_support( 'download',   'post-formats' );		
	}

	/**
	 * FES No Vendor Dashboard Set Notice.
	 *
	 * Shows an admin notice if the vendor dashboard
	 * page isn't set in the FES settings.
	 *
	 * @since 2.3.0
	 * @access public
	 * 
	 * @return void
	 */
	public function no_vendor_dashboard_set(){
		if ( ! EDD_FES()->helper->get_option( 'fes-vendor-dashboard-page', false ) ){
			echo '<div class="error"><p>';
				echo sprintf( __( 'Warning, the vendor dashboard page isn\'t set in the FES dashboard. This could lead to serious issues. %1$sSet it to the correct page as soon as possible%2$s.', 'edd_fes' ), '<a href="'. admin_url('admin.php?page=edd-settings&tab=fes&section=forms'). '">', '</a>' );
				echo '</p>';
			echo '</div>';
		}
	}
	
	/**
	 * No Media Editor Installed.
	 *
	 * FES requires sites meet the minimum requirements
	 * for WordPress. See https://github.com/chriscct7/edd-fes/issues/1010
	 *
	 * @since 2.4.0
	 * @access public
	 * 
	 * @return void
	 */
	public function test_if_editors_installed() {
		 $gd_installed = false;

		if ( extension_loaded('gd') && function_exists('gd_info') ) {
			$gd_installed = true;
		}

		$imagick_installed = false;

		if( extension_loaded('imagick') && class_exists("Imagick") ) {
			$imagick_installed = true;
		}

		if ( ( ! $gd_installed && ! $imagick_installed  ) || !wp_image_editor_supports( array( 'mime_type' => 'image/png' ) ) ){
				$message = __( "Warning: You have no valid editor extensions installed. Please install GD or Imagick", 'edd_fes');
				echo '<div class="error"><p>' . $message . '</p></div>';  
		}
	}

	/**
	 * FES Enqueue Form Assets.
	 *
	 * This function can be manually called
	 * to enqueue the scripts and styles needed for
	 * FES forms.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @return void
	 */
	public function enqueue_form_assets() {
		if ( !is_page( EDD_FES()->helper->get_option( 'fes-vendor-dashboard-page', false ) ) ) {
			EDD_FES()->setup->enqueue_styles( true );
			EDD_FES()->setup->enqueue_scripts( true );
		}
	}

	/**
	 * FES Enqueue Scripts.
	 *
	 * Loads the scripts FES needs on the
	 * frontend.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param bool $override If true load on page even if the
	 *                       page isn't the vendor dashboard.
	 * @return void
	 */
	public function enqueue_scripts( $override = false ) {
		if ( !fes_is_frontend() ) {
			return;
		}
		global $post;
		if ( is_page( EDD_FES()->helper->get_option( 'fes-vendor-dashboard-page', false ) ) || $override ) {
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'underscore' );
			// FES outputs minified scripts by default on the frontend. To load full versions, hook into this and return empty string.
			$minify = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) || ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
			$minify = apply_filters( 'fes_output_minified_versions', $minify );
			wp_enqueue_script( 'fes_form', fes_plugin_url . 'assets/js/frontend-form' . $minify . '.js', array(
					'jquery'
				), fes_plugin_version );

			$options = array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'error_message' => __( 'Please fix the errors to proceed', 'edd_fes' ),
				'nonce' => wp_create_nonce( 'fes_nonce' ),
				'avatar_title' =>  __( 'Choose an avatar', 'edd_fes' ),
				'avatar_button' =>  __( 'Select as avatar', 'edd_fes' ),
				'file_title' =>  __( 'Choose a file', 'edd_fes' ),
				'file_button' =>  __( 'Insert file URL', 'edd_fes' ),
				'feat_title' =>  __( 'Choose a featured image', 'edd_fes' ),
				'feat_button' =>  __( 'Select as featured image', 'edd_fes' ),
				'too_many_files_pt_1' => __( 'You may not add more than ', 'edd_fes' ),
				'too_many_files_pt_2' => __( ' files!', 'edd_fes' ),
				'errortitle' =>  __( 'Error!', 'edd_fes' ),
				'errormessage' =>  __( 'Please fix the errors to proceed', 'edd_fes' ),
				'ajaxerrortitle' =>  __( 'PHP Fatal Error:', 'edd_fes' ),
				'successtitle' =>  __( 'Success!', 'edd_fes' ),
				'successmessage' =>  __( 'It works!', 'edd_fes' ), // filter in each of the forms
				'loadingtext'    =>  __( 'Loading', 'edd_fes' ),
				'skipswal'  => false,
				'loading_icon' => '',
			);
			
			if ( fes_easter_egg_mode() ){
				$options['loading_icon'] = fes_plugin_url . 'assets/img/loading.gif';
			}
			
			$options = apply_filters( 'fes_forms_options_frontend', $options );
			wp_localize_script( 'fes_form', 'fes_form', $options );
			wp_enqueue_script( 'fes_sw', fes_plugin_url . 'assets/js/sw.js', array( 'jquery'), fes_plugin_version );
			wp_enqueue_script( 'fes_spin', fes_plugin_url . 'assets/js/spin.min.js', array( 'jquery'), fes_plugin_version );
			wp_enqueue_script( 'fes_spinner', fes_plugin_url . 'assets/js/spinner.js', array( 'jquery'), fes_plugin_version );
			wp_enqueue_media();
			wp_enqueue_script( 'comment-reply' );
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_script( 'jquery-ui-autocomplete' );
			wp_enqueue_script( 'suggest' );
			wp_enqueue_script( 'jquery-ui-slider' );
			wp_enqueue_script( 'jquery-ui-timepicker', fes_plugin_url . 'assets/js/jquery-ui-timepicker-addon.js', array( 'jquery-ui-datepicker' ) );
		}
	}

	/**
	 * FES Enqueue Styles.
	 *
	 * Loads the styles FES needs on the
	 * frontend.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param bool $override If true load on page even if the
	 *                       page isn't the vendor dashboard.
	 * @return void
	 */
	public function enqueue_styles( $override = false ) {
		if ( !fes_is_frontend() ) {
			return;
		}
		global $post;
		if ( is_page( EDD_FES()->helper->get_option( 'fes-vendor-dashboard-page', false ) ) || $override ) {
			// FES outputs minified scripts by default on the frontend. To load full versions, hook into this and return empty string.
			$minify = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) || ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
			$minify = apply_filters( 'fes_output_minified_versions', $minify );
			if ( EDD_FES()->helper->get_option( 'fes-use-css', false ) === '1' ) {
				wp_enqueue_style( 'fes-css', fes_plugin_url . 'assets/css/frontend' . $minify . '.css', array(), fes_plugin_version );
			}
			wp_enqueue_style( 'jquery-ui', fes_plugin_url . 'assets/css/jquery-ui-1.9.1.custom.css', array(), fes_plugin_version );
			wp_enqueue_style( 'fes-spin-css', fes_plugin_url . 'assets/css/spin.css', array(), fes_plugin_version );
			wp_enqueue_style( 'fes-sw-css', fes_plugin_url . 'assets/css/sw.css', array(), fes_plugin_version );
		}
	}

	/**
	 * FES Admin Enqueue Scripts.
	 *
	 * Loads the scripts FES needs on the
	 * admin.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts() {
		if ( !fes_is_admin() ) {
			return;
		}
		$current_screen = get_current_screen();
		$is_fes_page = false;
		$is_formbuilder = false;
		
		if ( is_object( $current_screen ) && isset( $current_screen->id ) && strlen( $current_screen->id ) > 5 && ( substr( $current_screen->id, 0, 7 ) === 'edd-fes' || $current_screen->id === 'toplevel_page_fes-about' ) ){
			$is_fes_page = true;
		} else if ( is_object( $current_screen ) && isset( $current_screen->post_type ) && $current_screen->post_type === 'fes-forms' ) { 
			$is_fes_page    = true;
			$is_formbuilder = true;
		} else if ( edd_is_admin_page( 'download' ) ) { 
			$is_fes_page = true;	
		} else if ( is_object( $current_screen ) && isset( $current_screen->id ) && ( $current_screen->id === 'profile' || $current_screen->id === 'user-edit' || $current_screen->id === 'user' || $current_screen->id === 'user-new' ) ) { 
			$is_fes_page = true;
		}

		if ( $is_fes_page ){
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'jquery-ui-autocomplete' );
			wp_enqueue_script( 'edd-admin-scripts' );
			wp_enqueue_script( 'jquery-smallipop', fes_plugin_url . 'assets/js/jquery.smallipop-0.4.0.min.js', array( 'jquery' ) );
			if ( $is_formbuilder ) {
				wp_enqueue_script( 'fes-formbuilder', fes_plugin_url . 'assets/js/formbuilder.js', array( 'jquery', 'jquery-ui-sortable' ) );
			}
			wp_register_script( 'jquery-tiptip', fes_plugin_url . 'assets/js/jquery-tiptip/jquery.tipTip.min.js', array( 'jquery' ), '2.0', true );
			wp_enqueue_script( 'fes-admin-js', fes_plugin_url . 'assets/js/admin.js', array( 'jquery', 'jquery-tiptip' ), '2.0', true );

			$options = array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'loadingtext'    =>  __( 'Loading', 'edd_fes' ),
				'vendor_status_change_title' => __( 'Are you sure?' , 'edd_fes' ),
				'vendor_status_change_message_start' => __( 'You\'re about to ','edd_fes' ),
				'vendor_status_create_vendor' => sprintf( _x( 'You\'re about to make this person a %s', 'FES lowercase singular setting for vendor','edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = false ) ),
				'vendor_status_create_vendor_success' => sprintf( _x( 'The user is now a %s', 'FES lowercase singular setting for vendor','edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = false ) ),
				'vendor_status_change_yes' => __( 'Yes', 'edd_fes' ),
				'vendor_status_change_message_end' => sprintf( _x( ' this %s ', 'FES lowercase singular setting for vendor','edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = false ) ),
				'vendor_status_change_yes' => __( 'Yes', 'edd_fes' ),
				'vendor_status_change_no' => __( 'No', 'edd_fes' ),
				'loading_icon' => '',
			);
			
			if ( fes_easter_egg_mode() ){
				$options['loading_icon'] = fes_plugin_url . 'assets/img/loading.gif';
			}
			
			$options = apply_filters( 'fes_admin_js_vars', $options );
			wp_localize_script( 'fes-admin-js', 'fes_admin', $options );

			wp_enqueue_script( 'jquery-ui-autocomplete' );
			wp_enqueue_script( 'underscore' );
			wp_enqueue_script( 'fes_form', fes_plugin_url . 'assets/js/frontend-form.js', array( 'jquery' ) );

			$options = array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'error_message' => __( 'Please fix the errors to proceed', 'edd_fes' ),
				'nonce' => wp_create_nonce( 'fes_nonce' ),
				'avatar_title' =>  __( 'Choose an avatar', 'edd_fes' ),
				'avatar_button' =>  __( 'Select as avatar', 'edd_fes' ),
				'file_title' =>  __( 'Choose a file', 'edd_fes' ),
				'file_button' =>  __( 'Insert file URL', 'edd_fes' ),
				'feat_title' =>  __( 'Choose a featured image', 'edd_fes' ),
				'feat_button' =>  __( 'Select as featured image', 'edd_fes' ),
				'too_many_files_pt_1' => __( 'You may not add more than ', 'edd_fes' ),
				'too_many_files_pt_2' => __( ' files!', 'edd_fes' ),
				'errortitle' =>  __( 'Error!', 'edd_fes' ),
				'errormessage' =>  __( 'Please fix the errors to proceed', 'edd_fes' ),
				'ajaxerrortitle' =>  __( 'PHP Fatal Error:', 'edd_fes' ),
				'successtitle' =>  __( 'Success!', 'edd_fes' ),
				'successmessage' =>  __( 'It works!', 'edd_fes' ), // filter in each of the forms
				'loadingtext'    =>  __( 'Loading', 'edd_fes' ),
				'skipswal'  => false,
				'loading_icon' => '',
			);
			
			if ( fes_easter_egg_mode() ){
				$options['loading_icon'] = fes_plugin_url . 'assets/img/loading.gif';
			}
			$options = apply_filters( 'fes_fes_forms_options_admin', $options );
			wp_localize_script( 'fes_form', 'fes_form', $options );

			wp_enqueue_script( 'fes_sw', fes_plugin_url . 'assets/js/sw.js', array( 'jquery'), fes_plugin_version );
			wp_enqueue_script( 'fes_spin', fes_plugin_url . 'assets/js/spin.min.js', array( 'jquery'), fes_plugin_version );
			wp_enqueue_script( 'fes_spinner', fes_plugin_url . 'assets/js/spinner.js', array( 'jquery'), fes_plugin_version );
			wp_enqueue_script( 'comment-reply' );
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_script( 'jquery-ui-autocomplete' );
			wp_enqueue_script( 'suggest' );
			wp_enqueue_media();
			wp_enqueue_script( 'jquery-ui-slider' );
			wp_enqueue_script( 'jquery-ui-timepicker', fes_plugin_url . 'assets/js/jquery-ui-timepicker-addon.js', array( 'jquery-ui-datepicker' ) );			
			wp_register_script( 'jquery-chosen', EDD_PLUGIN_URL . 'assets/js/chosen.jquery.js', array( 'jquery' ), EDD_VERSION );
			wp_enqueue_script( 'jquery-chosen' );
		}
	}

	/**
	 * FES Admin Enqueue Styles.
	 *
	 * Loads the styles FES needs on the
	 * admin.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function admin_enqueue_styles() {
		if ( !fes_is_admin() ) {
			return;
		}

		$current_screen = get_current_screen();
		$is_fes_page    = false;
		$is_formbuilder = false;
		$page           = isset( $_GET['page'] )       ? strtolower( $_GET['page'] )       : false;
		if ( is_object( $current_screen ) && isset( $current_screen->id ) && strlen( $current_screen->id ) > 5 && ( substr( $current_screen->id, 0, 7 ) === 'edd-fes' || $current_screen->id === 'toplevel_page_fes-about' ) ){
			$is_fes_page = true;
		} else if ( is_object( $current_screen ) && isset( $current_screen->post_type ) && $current_screen->post_type === 'fes-forms' ) { 
			$is_fes_page    = true;
			$is_formbuilder = true;
		} else if ( ! $page && ( edd_is_admin_page( 'download', 'list-table' ) ||  edd_is_admin_page( 'download', 'edit' ) ||  edd_is_admin_page( 'download', 'new' ) ) ) { 
			$is_fes_page = true;	
		} else if ( is_object( $current_screen ) && isset( $current_screen->id ) && ( $current_screen->id === 'profile' || $current_screen->id === 'user-edit' || $current_screen->id === 'user' || $current_screen->id === 'user-new' ) ) { 
			$is_fes_page = true;
		}

		if ( $is_fes_page ){
			if ( $is_formbuilder ) {
				wp_enqueue_style( 'fes-formbuilder', fes_plugin_url . 'assets/css/formbuilder.css' );
			}
			edd_register_styles();
			wp_enqueue_style( 'fes-css', fes_plugin_url . 'assets/css/frontend.css', array(), fes_plugin_version );
			wp_enqueue_style( 'fes-admin-css', fes_plugin_url . 'assets/css/admin.css', array(), fes_plugin_version );
			wp_enqueue_style( 'jquery-smallipop', fes_plugin_url . 'assets/css/jquery.smallipop.css', array(), fes_plugin_version );
			wp_enqueue_style( 'jquery-ui-core', fes_plugin_url . 'assets/css/jquery-ui-1.9.1.custom.css', array(), fes_plugin_version );
			wp_enqueue_style( 'fes-spin-css', fes_plugin_url . 'assets/css/spin.css', array(), fes_plugin_version );
			wp_enqueue_style( 'fes-sw-css', fes_plugin_url . 'assets/css/sw.css', array(), fes_plugin_version );
			wp_register_style( 'jquery-chosen', EDD_PLUGIN_URL . 'assets/css/chosen.css', array(), EDD_VERSION );
			wp_enqueue_style( 'jquery-chosen' );
		}
	}

	/**
	 * FES Version meta generator.
	 *
	 * Outputs FES version for support
	 * purposes.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function fes_version() {
		// Newline on both sides to avoid being in a blob
		echo '<meta name="generator" content="EDD FES v' . fes_plugin_version . '" />' . "\n";
	}

	/**
	 * FES admin head.
	 *
	 * This is used to output CSS used to make
	 * the custom FES dashicon.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @return void
	 */
	public function admin_head() {
?>
	<style>
	@charset "UTF-8";

	@font-face {
		font-family: "fes";
		src:url("<?php echo fes_assets_url; ?>/font/fes-dashicon.eot");
		src:url("<?php echo fes_assets_url; ?>/font/fes-dashicon.eot?#iefix") format("embedded-opentype"),
			url("<?php echo fes_assets_url; ?>/font/fes-dashicon.woff") format("woff"),
			url("<?php echo fes_assets_url; ?>/font/fes-dashicon.ttf") format("truetype"),
			url("<?php echo fes_assets_url; ?>/font/fes-dashicon.svg#fes") format("svg");
		font-weight: normal;
		font-style: normal;

	}

	#toplevel_page_fes-about [data-icon]:before {
		font-family: "fes" !important;
		content: attr(data-icon);
		font-style: normal !important;
		font-weight: normal !important;
		font-variant: normal !important;
		text-transform: none !important;
		speak: none;
		line-height: 1;
		-webkit-font-smoothing: antialiased;
		-moz-osx-font-smoothing: grayscale;
	}

	#toplevel_page_fes-about [class^="icon-"]:before,
	#toplevel_page_fes-about [class*=" icon-"]:before {
		font-family: "fes" !important;
		font-style: normal !important;
		font-weight: normal !important;
		font-variant: normal !important;
		text-transform: none !important;
		speak: none;
		line-height: 1;
		-webkit-font-smoothing: antialiased;
		-moz-osx-font-smoothing: grayscale;
	}
	#adminmenu #toplevel_page_fes-about .menu-icon-generic div.wp-menu-image:before {
		font-family: "fes" !important;
		content: "a";
	}
	</style>
	<?php
	}

	/**
	 * FES Remove Media Library Tab.
	 *
	 * Removes the library, gallery, type, type url,
	 * and url tabs from the media library on the 
	 * frontend if the current user is not an admin.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param array $tabs Tabs to show on the media modal.
	 * @return array Tabs to show on the media modal.
	 */
	public function remove_media_library_tab( $tabs ) {
		if ( !fes_is_frontend() ) {
			return $tabs;
		}
		if ( ! EDD_FES()->vendors->vendor_is_admin() ) {
			unset( $tabs['library'] );
			unset( $tabs['gallery'] );
			unset( $tabs['type'] );
			unset( $tabs['type_url'] );
			return $tabs;
		} else {
			return $tabs;
		}
	}

	/**
	 * FES Restrict Media.
	 *
	 * Prevents vendors from seeing media files that aren't theirs
	 * if the current user isn't an admin.
	 *
	 * @since 2.0.0
	 * @since 2.4.0 Only filter on ajax requested attachments.
	 * @access public
	 *
	 * @param array $query Query to retrieve media items.
	 * @return void
	 */
	public function restrict_media( $query ) {
		if ( fes_is_admin() ) {
			return $query;
		}

		if ( ! EDD_FES()->vendors->user_is_admin() ) {
			$user_id = get_current_user_id();
			if ( $user_id ) {
				$query['author'] = $user_id;
			}
		}
		return $query;
	}

	/**
	 * FES Load Fields.
	 *
	 * Loads the abstract and then all of the extended
	 * FES Fields.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @return void
	 */
	public function load_fields() {
		// require abstract
		require_once fes_plugin_dir . 'classes/abstracts/class-field.php';

		// require fields
		require_once fes_plugin_dir . 'classes/fields/text.php';
		require_once fes_plugin_dir . 'classes/fields/textarea.php';
		require_once fes_plugin_dir . 'classes/fields/post_title.php';
		require_once fes_plugin_dir . 'classes/fields/action_hook.php';
		require_once fes_plugin_dir . 'classes/fields/checkbox.php';
		require_once fes_plugin_dir . 'classes/fields/country.php';
		require_once fes_plugin_dir . 'classes/fields/date.php';
		require_once fes_plugin_dir . 'classes/fields/display_name.php';
		require_once fes_plugin_dir . 'classes/fields/download_category.php';
		require_once fes_plugin_dir . 'classes/fields/download_notes.php';
		require_once fes_plugin_dir . 'classes/fields/download_tag.php';
		require_once fes_plugin_dir . 'classes/fields/email.php';
		require_once fes_plugin_dir . 'classes/fields/featured_image.php';
		require_once fes_plugin_dir . 'classes/fields/file_upload.php';
		require_once fes_plugin_dir . 'classes/fields/first_name.php';
		require_once fes_plugin_dir . 'classes/fields/last_name.php';
		require_once fes_plugin_dir . 'classes/fields/name.php';
		require_once fes_plugin_dir . 'classes/fields/hidden.php';
		require_once fes_plugin_dir . 'classes/fields/multiple_pricing.php';
		require_once fes_plugin_dir . 'classes/fields/multiselect.php';
		require_once fes_plugin_dir . 'classes/fields/nickname.php';
		require_once fes_plugin_dir . 'classes/fields/password.php';
		require_once fes_plugin_dir . 'classes/fields/honeypot.php';
		require_once fes_plugin_dir . 'classes/fields/html.php';
		require_once fes_plugin_dir . 'classes/fields/post_content.php';
		require_once fes_plugin_dir . 'classes/fields/post_excerpt.php';
		require_once fes_plugin_dir . 'classes/fields/post_format.php';
		require_once fes_plugin_dir . 'classes/fields/last_name.php';
		require_once fes_plugin_dir . 'classes/fields/radio.php';
		require_once fes_plugin_dir . 'classes/fields/recaptcha.php';
		require_once fes_plugin_dir . 'classes/fields/repeat.php';
		require_once fes_plugin_dir . 'classes/fields/section_break.php';
		require_once fes_plugin_dir . 'classes/fields/select.php';
		require_once fes_plugin_dir . 'classes/fields/taxonomy.php';
		require_once fes_plugin_dir . 'classes/fields/toc.php';
		require_once fes_plugin_dir . 'classes/fields/url.php';
		require_once fes_plugin_dir . 'classes/fields/user_avatar.php';
		require_once fes_plugin_dir . 'classes/fields/user_bio.php';
		require_once fes_plugin_dir . 'classes/fields/user_email.php';
		require_once fes_plugin_dir . 'classes/fields/user_login.php';
		require_once fes_plugin_dir . 'classes/fields/user_url.php';

		/**
		 * FES Load Fields Require
		 *
		 * To add a custom FES Field, you should hook into this 
		 * action and require_once your field here. Warning to devs:
		 * See "Planned Potentially Breaking Changes" section in README.
		 *
		 * @since 2.3.0
		 */
		do_action( 'fes_load_fields_require' );

		/**
		 * FES Load Fields Array
		 *
		 * To add a custom FES Field, you should hook into this 
		 * filter and add your template -> class relationship.
		 *
		 * @since 2.3.0
		 *
		 * @param array $fields Template -> Class array.
		 */
		$fields = apply_filters( 'fes_load_fields_array',
			array(
				'action_hook'		  => 'FES_Action_Hook_Field',
				'checkbox'			  => 'FES_Checkbox_Field',
				'country'			  => 'FES_Country_Field',
				'date'				  => 'FES_Date_Field',
				'display_name'		  => 'FES_Display_Name_Field',
				'download_category'	  => 'FES_Download_Category_Field',
				'download_notes'	  => 'FES_Download_Notes_Field',
				'download_tag'		  => 'FES_Download_Tag_Field',
				'email'		 		  => 'FES_Email_Field',
				'featured_image'	  => 'FES_Featured_Image_Field',
				'file_upload'		  => 'FES_File_Upload_Field',
				'first_name'		  => 'FES_First_Name_Field',
				'hidden'			  => 'FES_Hidden_Field',
				'honeypot'			  => 'FES_Honeypot_Field',
				'html'				  => 'FES_HTML_Field',
				'last_name'			  => 'FES_Last_Name_Field',
				'multiple_pricing'	  => 'FES_Multiple_Pricing_Field',
				'multiselect'		  => 'FES_Multiselect_Field',
				'name'			  	  => 'FES_Name_Field',
				'nickname'			  => 'FES_Nickname_Field',
				'password'			  => 'FES_Password_Field',
				'post_content'		  => 'FES_Post_Content_Field',
				'post_excerpt'		  => 'FES_Post_Excerpt_Field',
				'post_format'	  	  => 'FES_Post_Format_Field',
				'post_title'		  => 'FES_Post_Title_Field',
				'radio'		 		  => 'FES_Radio_Field',
				'recaptcha'			  => 'FES_Recaptcha_Field',
				'repeat'			  => 'FES_Repeat_Field',
				'section_break'       => 'FES_Section_Break_Field',
				'select'        	  => 'FES_Select_Field',
				'taxonomy'	  		  => 'FES_Taxonomy_Field',
				'text'          	  => 'FES_Text_Field',
				'textarea'        	  => 'FES_Textarea_Field',
				'toc'                 => 'FES_Toc_Field',
				'url'           	  => 'FES_Url_Field',
				'user_avatar'		  => 'FES_User_Avatar_Field',
				'user_bio'            => 'FES_User_Bio_Field',
				'user_email'          => 'FES_User_Email_Field',
				'user_login'          => 'FES_User_Login_Field',
				'user_url'            => 'FES_User_Url_Field',
			)
		);
		return $fields;
	}

	/**
	 * FES Load Forms.
	 *
	 * Loads the abstract and then all of the extended
	 * FES Forms.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @return void
	 */
	public function load_forms() {
		// require abstract
		require_once fes_plugin_dir . 'classes/abstracts/class-form.php';

		// require forms
		require_once fes_plugin_dir . 'classes/forms/submission.php';
		require_once fes_plugin_dir . 'classes/forms/registration.php';
		require_once fes_plugin_dir . 'classes/forms/profile.php';
		require_once fes_plugin_dir . 'classes/forms/vendor_contact.php';
		require_once fes_plugin_dir . 'classes/forms/login.php';

		// do_action( 'fes_load_forms_require' ); Allow starting 2.4

		// get names ( name -> class)
		/*  Allow starting 2.4
		$forms = apply_filters( 'fes_load_forms_array', array( 
			'submission'	 => 'FES_Submission_Form',
			'registration'   => 'FES_Registration_Form',
			'profile' 		 => 'FES_Profile_Form',
			'login' 		 => 'FES_Login_Form',
			'vendor-contact' => 'FES_Vendor_Contact_Form',
		) ); */
		$forms = array(
			'submission'	 => 'FES_Submission_Form',
			'registration'   => 'FES_Registration_Form',
			'profile' 		 => 'FES_Profile_Form',
			'login' 		 => 'FES_Login_Form',
			'vendor-contact' => 'FES_Vendor_Contact_Form',
		);

		return $forms;
	}
}
