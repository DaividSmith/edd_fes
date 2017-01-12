<?php
/**
 * Plugin Name:         Easy Digital Downloads - Frontend Submissions
 * Plugin URI:          https://easydigitaldownloads.com/extensions/frontend-submissions/
 * Description:         Mimick Etsy, Envato, or Amazon type sites with this plugin and Easy Digital Downloads combined!
 * Author:              Chris Christoff
 * Author URI:          http://www.chriscct7.com
 *
 * Version:             2.4.5
 * Requires at least:   4.2
 * Tested up to:        4.6
 *
 * Text Domain:         edd_fes
 * Domain Path:         /languages/
 *
 * @category            Plugin
 * @copyright           Copyright © 2015 Chris Christoff
 * @author              Chris Christoff
 * @package             FES
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Loads this file which is used in the check to see if Easy Digital Downloads is active
 */
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

/**
 * The main EDD FES class
 *
 * This class loads all of the FES files and constants as well
 * as loads the l10n files.
 *
 * @since 2.0.0
 * @access public
 */
class EDD_Front_End_Submissions {

	/**
	 * FES plugin object
	 *
	 * @since 2.0.0
	 * @access public
	 * @var EDD_Front_End_Submissions $instance Singleton object of FES.
	 *      Use it to call all FES methods instead of calling FES functions directly.
	 */
	private static $instance;

	/**
	 * FES plugin id string
	 *
	 * @since 2.0.0
	 * @access public
	 * @var string $id
	 */
	public $id = 'edd_fes';

	/**
	 * FES plugin basename
	 *
	 * @since 2.0.0
	 * @access public
	 * @var string $basename
	 */
	public $basename;

	/**
	 * FES Forms object
	 *
	 * @since 2.0.0
	 * @access public
	 * @var FES_Forms $forms Use to access any function in FES_Forms class.
	 */
	public $forms;

	/**
	 * FES Templates
	 *
	 * @since 2.0.0
	 * @access public
	 * @var FES_Templates $templates Use to access any function in FES_Templates class.
	 */
	public $templates;

	/**
	 * FES Setup
	 *
	 * @since 2.0.0
	 * @access public
	 * @var FES_Setup $setup Use to access any function in FES_Setup class.
	 */
	public $setup;

	/**
	 * FES Emails
	 *
	 * @since 2.0.0
	 * @access public
	 * @var FES_Emails $emails Use to access any function in FES_Emails class.
	 */
	public $emails;

	/**
	 * FES Vendors
	 *
	 * @since 2.0.0
	 * @access public
	 * @var FES_Vendors $vendors Use to access any function in FES_Vendors class.
	 */
	public $vendors;

	/**
	 * FES Vendor Shop
	 *
	 * @since 2.0.0
	 * @access public
	 * @var FES_Vendor_Shop $vendor_shop Use to access any function in FES_Vendor_Shop class.
	 */
	public $vendor_shop;

	/**
	 * FES Dashboard
	 *
	 * @since 2.0.0
	 * @access public
	 * @var FES_Dashboard $dashboard Use to access any function in FES_Dashboard class.
	 */
	public $dashboard;

	/**
	 * FES Menu
	 *
	 * @since 2.3.0
	 * @access public
	 * @var FES_Menu $menu Use to access any function in FES_Menu class.
	 */
	public $menu;

	/**
	 * FES Helper
	 *
	 * @since 2.3.0
	 * @access public
	 * @var FES_Helper $helper Use to access any function in FES_Helper class.
	 */
	public $helper;

	/**
	 * FES Download Table
	 *
	 * @since 2.3.0
	 * @access public
	 * @var FES_Download_Table $download_table Use to access any function in FES_Download_Table class.
	 */
	public $download_table;

	/**
	 * FES Edit Download
	 *
	 * @since 2.0.0
	 * @access public
	 * @var FES_Edit_Download $edit_download Use to access any function in FES_Edit_Download class.
	 */
	public $edit_download;

	/**
	 * FES Forms objects
	 *
	 * @since 2.0.0
	 * @access public
	 * @var array $forms Contains array of each registered FES Form as empty
	 *					 instantiated object.
	 */
	public $load_forms;

	/**
	 * FES Field objects
	 *
	 * @since 2.0.0
	 * @access public
	 * @var array $forms Contains array of each registered FES Field as empty
	 *					 instantiated object.
	 */
	public $load_fields;

	/**
	 * FES Settings
	 *
	 * @since 2.0.0
	 * @access public
	 * @var FES_Settings $settings Use to access any function in FES_Settings class.
	 */
	public $settings;

	/**
	 * FES Formbuilder Templates
	 *
	 * @since 2.0.0
	 * @access public
	 * @var FES_Formbuilder_Templates $formbuilder_templates Use to access any function in FES_Formbuilder_Templates class.
	 */
	public $formbuilder_templates;

	/**
	 * FES Options
	 *
	 * @since 2.0.0
	 * @access public
	 * @deprecated 2.2.0 Use EDD_FES()->$settings
	 * @see EDD_FES()->$settings
	 * @var FES_Settings $settings Use to access any function in FES_Settings class.
	 *							   Only here for backwards compatibility.
	 */
	public $fes_options;

	/**
	 * Main EDD_Front_End_Submissions Instance
	 *
	 * Insures that only one instance of EDD_Front_End_Submissions exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @uses EDD_Front_End_Submissions::define_globals() Setup the globals needed
	 * @uses fes_call_install() Run a version to version background upgrade routine if required
	 * @uses EDD_Front_End_Submissions::includes() Include the required files
	 * @uses EDD_Front_End_Submissions::setup() Setup the hooks and actions
	 * @uses EDD_Front_End_Submissions::wp_notice() Throws admin notice if WP version < FES min WP version
	 * @uses EDD_Front_End_Submissions::edd_notice() Throws admin notice if EDD version < FES min EDD version
	 *
	 * @var array $instance
	 * @global string $wp_version WordPress version (provided by WordPress core).
	 * @return EDD_Front_End_Submissions The one true instance of EDD_Front_End_Submissions
	 */
	public static function instance() {
		global $wp_version;

		// If the WordPress site doesn't meet the correct EDD and WP version requirements, deactivate and show notice.
		if ( version_compare( $wp_version, '4.2', '<' ) ) {
			add_action( 'admin_notices', array('EDD_Front_End_Submissions','wp_notice' ) );
			return;
		} else if ( !class_exists( 'Easy_Digital_Downloads' ) || version_compare( EDD_VERSION, '2.4', '<' ) ) {
			add_action( 'admin_notices', array('EDD_Front_End_Submissions','edd_notice' ) );
			return;
		}

		if ( !isset( self::$instance ) && !( self::$instance instanceof EDD_Front_End_Submissions ) ) {
			self::$instance = new EDD_Front_End_Submissions;
			self::$instance->define_globals();
			self::$instance->includes();

			$fes_version = get_option( 'fes_current_version', '2.1' );

			// this does the version to version background update routines including schema correction
			if ( version_compare( $fes_version, '2.4.3', '<' ) ) {
				fes_call_install();
			}

			self::$instance->setup();

			/*
			 * Here we're loading all of the registered FES Form and Field objects.
			 * We do this on an add_action on plugins_loaded to ensure other plugins
			 * can register custom FES Form and FES Field classes. The plugins_loaded
			 * makes it so we don't run too early for this.
			 */
			add_action( 'plugins_loaded', array( self::$instance, 'load_forms_and_fields' ) );

			// Setup class instances
			self::$instance->helper 			   = new FES_Helpers;
			self::$instance->emails                = new FES_Emails;
			self::$instance->vendors               = new FES_Vendors;
			self::$instance->integrations		   = new FES_Integrations;
			self::$instance->settings			   = new FES_Settings;
			self::$instance->fes_options		   = self::$instance->helper; // Backwards compatibility

			if ( fes_is_admin() ) {
				self::$instance->menu                  = new FES_Menu;
				self::$instance->download_table        = new FES_Download_Table;
				self::$instance->edit_download         = new FES_Edit_Download;
				self::$instance->formbuilder_templates = new FES_Formbuilder_Templates;
			}

			self::$instance->templates             = new FES_Templates;
			self::$instance->vendor_shop           = new FES_Vendor_Shop;
			self::$instance->dashboard             = new FES_Dashboard;
			self::$instance->forms		           = new FES_Forms;

			/*
			 * We have to load EDD's upload functions and misc functions files manually
			 * to garuntee that everywhere in FES we can use those functions
			 */
			require_once EDD_PLUGIN_DIR . 'includes/admin/upload-functions.php';
			require_once EDD_PLUGIN_DIR . 'includes/misc-functions.php';
		}
		return self::$instance;
	}

	/**
	 * Throw error on object clone
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @return void
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'edd_fes' ), '2.3' );
	}

	/**
	 * Disable unserializing of the class
	 *
	 * Attempting to wakeup an FES instance will throw a doing it wrong notice.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @return void
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'edd_fes' ), '2.3' );
	}

	/**
	 * Define FES globals
	 *
	 * This function defines all of the FES PHP constants and a few object attributes.
	 *
	 * @since 2.2.0
	 * @access public
	 *
	 * @return void
	 */
	public function define_globals() {
		$this->title	= __( 'Frontend Submissions', 'edd_fes' );
		$this->file		= __FILE__;
		$basename		= plugin_basename( $this->file );

		/**
		 * FES basename.
		 *
		 * This filter allows you to edit the FES object basename field.
		 *
		 * @since 2.0.0
		 *
		 * @param string  $basename Basename of FES.
		 */
		$this->basename = apply_filters( 'fes_plugin_basename', $basename );

		// Plugin Name
		if ( !defined( 'fes_plugin_name' ) ) {
			define( 'fes_plugin_name', 'Frontend Submissions' );
		}
		// Plugin Version
		if ( !defined( 'fes_plugin_version' ) ) {
			define( 'fes_plugin_version', '2.4.5' );
		}
		// Plugin Root File
		if ( !defined( 'fes_plugin_file' ) ) {
			define( 'fes_plugin_file', __FILE__ );
		}
		// Plugin Folder Path
		if ( !defined( 'fes_plugin_dir' ) ) {
			define( 'fes_plugin_dir', plugin_dir_path( fes_plugin_file ) );
		}
		// Plugin Folder URL
		if ( !defined( 'fes_plugin_url' ) ) {
			define( 'fes_plugin_url', plugin_dir_url( fes_plugin_file ) );
		}
		// Plugin Assets URL
		if ( !defined( 'fes_assets_url' ) ) {
			define( 'fes_assets_url', fes_plugin_url . 'assets/' );
		}
	}

	/**
	 * Load FES form and fields
	 *
	 * This function defines all of the FES PHP constants and a few object attributes.
	 *
	 * @since 2.3.0
	 * @uses FES_Setup::load_forms() Load all FES Form classes.
	 * @uses FES_Setup::load_fields() Load all FES Field classes.
	 *
	 * @access public
	 * @return void
	 */
	public function load_forms_and_fields(){
		// load form abstract and extending forms
		self::$instance->load_forms 	= self::$instance->setup->load_forms();

		// load field abstract and extending fields
		self::$instance->load_fields 	= self::$instance->setup->load_fields();
	}

	/**
	 * Load FES files
	 *
	 * This function loads the majority of FES's files.
	 *
	 * @since 2.3.0
	 * @access public
	 * @todo Use better check for admin (fes_is_admin())
	 *
	 * @return void
	 */
	public function includes() {
		require_once fes_plugin_dir . 'classes/class-helpers.php';
		require_once fes_plugin_dir . 'classes/class-vendor.php';
		require_once fes_plugin_dir . 'classes/class-db-vendor.php';
		require_once fes_plugin_dir . 'classes/class-vendors.php';
		require_once fes_plugin_dir . 'classes/class-emails.php';
		require_once fes_plugin_dir . 'classes/class-integrations.php';
		require_once fes_plugin_dir . 'classes/misc-functions.php';

		if ( !class_exists( 'WP_List_Table' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
		}
		require_once fes_plugin_dir . 'classes/admin/class-update.php';
		require_once fes_plugin_dir . 'classes/schema.php';

		if ( is_admin() ){
			require_once fes_plugin_dir . 'classes/admin/vendors/vendors.php';
			require_once fes_plugin_dir . 'classes/admin/vendors/vendor-actions.php';
			require_once fes_plugin_dir . 'classes/admin/vendors/vendor-functions.php';
			require_once fes_plugin_dir . 'classes/admin/vendors/graphing.php';
			require_once fes_plugin_dir . 'classes/admin/vendors/class-export-customers.php';
			require_once fes_plugin_dir . 'classes/admin/vendors/pdf-reports.php';
			require_once fes_plugin_dir . 'classes/admin/class-tracking.php';
			require_once fes_plugin_dir . 'classes/admin/class-welcome.php';
			require_once fes_plugin_dir . 'classes/admin/class-tools.php';
			require_once fes_plugin_dir . 'classes/admin/class-menu.php';
			require_once fes_plugin_dir . 'classes/admin/class-list-table.php';
			require_once fes_plugin_dir . 'classes/admin/downloads/class-download-table.php';
			require_once fes_plugin_dir . 'classes/admin/downloads/class-edit-download.php';
			require_once fes_plugin_dir . 'classes/admin/vendors/class-vendor-table.php';
			require_once fes_plugin_dir . 'classes/admin/formbuilder/class-formbuilder.php';
			require_once fes_plugin_dir . 'classes/admin/formbuilder/class-formbuilder-templates.php';
		}

		require_once fes_plugin_dir . 'classes/frontend/class-vendor-shop.php';
		require_once fes_plugin_dir . 'classes/frontend/class-templates.php';
		require_once fes_plugin_dir . 'classes/frontend/class-dashboard.php';
		require_once fes_plugin_dir . 'classes/frontend/class-forms.php';

		// After loading all files, let's add the FES templates directories to the EDD template directory list.
		add_filter( 'edd_template_paths', array( $this, 'edd_template_paths' ) );
	}

	/**
	 * Initial FES setup
	 *
	 * This function sets up FES's post type, runs the hooks/filters in the FES_Setup
	 * class, loads the textdomain, loads the FES settings, and registers EDD license checks
	 * for the FES plugin.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @uses FES_Setup::load_settings() Loads FES settings
	 *
	 * @return void
	 */
	public function setup() {
		require_once fes_plugin_dir . 'classes/class-post-types.php';
		require_once fes_plugin_dir . 'classes/class-setup.php';

		// load textdomains
		$this->load_textdomain();

		// load settings
		$this->load_settings();
		self::$instance->setup = $this->setup = new FES_Setup;

		// load license
		if ( class_exists( 'EDD_License' ) ) {
			$license = new EDD_License( __FILE__, fes_plugin_name, fes_plugin_version, 'Chris Christoff' );
		}
	}

	/**
	 * Load FES Textdomain
	 *
	 * This function attempts to find FES translation files and load them. It
	 * uses a system similar to EDD core's.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function load_textdomain() {
		// This filter is already documented in WordPress core
		$locale        = apply_filters( 'plugin_locale', 'en_US', 'edd_fes' );

		$mofile        = sprintf( '%1$s-%2$s.mo', 'edd_fes', $locale );

		$mofile_local  = trailingslashit( fes_plugin_dir . 'languages' ) . $mofile;
		$mofile_global = WP_LANG_DIR . '/edd_fes/' . $mofile;

		if ( file_exists( $mofile_global ) ) {
			return load_textdomain( 'edd_fes', $mofile_global );
		} else if ( file_exists( $mofile_local ) ) {
			return load_textdomain( 'edd_fes', $mofile_local );
		} else{
			load_plugin_textdomain( 'edd_fes', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}
	}

	/**
	 * Add FES template path to EDD template paths
	 *
	 * This function adds FES's template paths to EDD's places to
	 * look for template paths.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param array $paths Paths to try.
	 * @return void
	 */
	public function edd_template_paths( $paths ) {
		$paths[80] = trailingslashit( fes_plugin_dir ) . trailingslashit( 'templates' );
		return $paths;
	}

	/**
	 * Loads FES settings
	 *
	 * This function adds FES's settings to the EDD settings panel. For
	 * backwards compatibility reasons it also allows you to continue
	 * using FES_Settings class functions.
	 *
	 * @since 2.0.0
	 * @since 2.4.0 Adds settings to EDD core settings panel, and removes Redux.
	 * @access public
	 *
	 * @return void
	 */
	public function load_settings() {
		global $fes_settings;
		$fes_settings = edd_get_settings();
		require_once fes_plugin_dir . 'classes/admin/class-settings.php';
	}

	/**
	 * FES minimum EDD version notice
	 *
	 * This function is used to throw an admin notice when the WordPress install
	 * does not meet FES's minimum EDD version requirements.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @return void
	 */
	public static function edd_notice() { ?>
		<div class="updated">
			<p><?php printf( __( '<strong>Notice:</strong> Easy Digital Downloads Frontend Submissions requires Easy Digital Downloads 2.5 or higher in order to function properly.', 'edd_fes' ) ); ?></p>
		</div>
		<?php
	}

	/**
	 * FES minimum WP version notice
	 *
	 * This function is used to throw an admin notice when the WordPress install
	 * does not meet FES's minimum WP version requirements.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @return void
	 */
	public static function wp_notice() { ?>
		<div class="updated">
			<p><?php printf( __( '<strong>Notice:</strong> Easy Digital Downloads Frontend Submissions requires WordPress 4.2 or higher in order to function properly.', 'edd_fes' ) ); ?></p>
		</div>
		<?php
	}
}

/**
 * The main function responsible for returning the one true EDD_Front_End_Submissions
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $edd_fes = EDD_FES(); ?>
 *
 * @since 2.0.0
 *
 * @uses EDD_Front_End_Submissions::instance() Retrieve FES instance.
 *
 * @return EDD_Front_End_Submissions The singleton FES instance.
 */
function EDD_FES() {
	return EDD_Front_End_Submissions::instance();
}

EDD_FES();

/**
 * FES Install
 *
 * This function is used install FES
 *
 * @since 2.0.0
 * @access public
 *
 * @global string $wp_version WordPress version (provided by WordPress core).
 * @uses EDD_Front_End_Submissions::load_settings() Loads FES settings
 * @uses FES_Install::init() Runs install process
 *
 * @return void
 */
function FES_Install() {
	global $wp_version;

	// If the WordPress site doesn't meet the correct EDD and WP version requirements, don't activate FES
	if ( version_compare( $wp_version, '4.2', '<' ) ) {
		if ( is_plugin_active( EDD_FES()->basename ) ) {
			return;
		}
	} else if ( !class_exists( 'Easy_Digital_Downloads' ) || version_compare( EDD_VERSION, '2.4', '<' ) ) {
		if ( is_plugin_active( EDD_FES()->basename ) ) {
			return;
		}
	}

	// Load settings (so we can use/set them during the install process)
	EDD_FES()->load_settings();

	// Load schema.php (contains initial FES Form schema as well as install functions)
	require_once fes_plugin_dir . 'classes/schema.php';

	// Load the FES_Forms post type
	require_once fes_plugin_dir . 'classes/class-post-types.php';

	// Load the FES_Install class
	require_once fes_plugin_dir . 'classes/admin/class-install.php';

	// Run the FES install
	$install = new FES_Install;
	$install->init();
}

/**
 * FES check for update processes
 *
 * This function is used to call the FES install class, which in turn
 * checks to see if there are any update procedures to be run, and if
 * so runs them
 *
 * @since 2.3.0
 * @access public
 *
 * @uses FES_Install() Runs install process
 *
 * @return void
 */
function fes_call_install(){
	add_action( 'wp_loaded', 'FES_Install' );
}
