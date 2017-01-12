<?php
/**
 * FES Welcome Screen
 *
 * This file deals with FES's welcome screen.
 *
 * @package FES
 * @subpackage Administration
 * @since 2.2.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FES Welcome.
 *
 * Shows the FES welcome screen.
 *
 * @since 2.2.0
 * @access public
 */
class FES_Welcome {

	/**
	 * FES Welcome Screen Actions.
	 *
	 * Runs actions required to show
	 * the FES welcome screen.
	 *
	 * @since 2.2.0
	 * @access public
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'admin_head', array( $this, 'admin_head' ) );
		add_action( 'admin_init', array( $this, 'welcome'    ) );
	}

	/**
	 * FES Welcome Screen CSS.
	 *
	 * Adds CSS for the welcome page.
	 *
	 * @since 2.2.0
	 * @access public
	 *
	 * @return void
	 */
	public function admin_head() {
		// Badge for welcome page
		$page = get_current_screen();
		if ( isset( $page->id  ) && $page->id == 'toplevel_page_fes-about' ) {
			?>
			<style type="text/css" media="screen">
				/*<![CDATA[*/
				.about-wrap .fes-header-heading { margin-bottom: 15px; }
				.about-wrap .fes-new-badge { float: right; border-radius: 4px; margin: 0 0 15px 15px; max-width: 200px; }
				.about-wrap .about-text { margin: 0 0 15px; max-width: 500px; }
				.about-wrap h2.nav-tab-wrapper { float: left; margin-bottom: 20px; width: 100%; }
				.about-wrap .fes-tab-content,
				.about-wrap div.updated,
				.about-wrap div.error,
				.about-wrap .notice,
				.about-wrap .about-description { clear: both; margin-top: 20px; }
				.about-wrap .fes-about-section { padding-top: 20px; }
				.about-wrap .fes-about-section .media-container { border: 1px solid #ddd; border-radius: 3px; margin-bottom: 10px; }
				.about-wrap .feature-section.two-col .col { vertical-align: top; }
				.about-wrap .feature-section-content,
				.about-wrap .feature-section-media { width: 50%; box-sizing: border-box; }
				.about-wrap .feature-section-content { float: left; padding-right: 50px; }
				.about-wrap .feature-section-content h4 { margin: 0 0 1em; }
				.about-wrap .feature-section-media { float: right; text-align: right; margin-bottom: 20px; }
				.about-wrap .fes-changelog { font-size: 14px; line-height: 1.6; padding-left: 20px; }
				.about-wrap .fes-changelog-title { font-size: 20px; margin: 0 0 0 -20px; }
				/* responsive */
				@media all and ( max-width: 782px ) {
					.about-wrap .fes-new-badge { max-width: 120px; }
					.about-wrap .feature-section-content,
					.about-wrap .feature-section-media { float: none; padding-right: 0; width: 100%; text-align: left; }
					.about-wrap .feature-section-media img { float: none; margin: 0 0 20px; }
				}
				/*]]>*/
			</style>
			<?php
		}
	}

	/**
	 * FES Welcome Screen Tabs.
	 *
	 * Outputs the navigation tabs
	 * on the FES welcome screen.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @return void
	 */
	public function tabs() {
		$selected = isset( $_GET['tab'] ) ? $_GET['tab'] : 'fes-about';
		?>
		<h2 class="nav-tab-wrapper">
			<a class="nav-tab <?php echo $selected == 'fes-about' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'fes-about', 'tab' => 'fes-about' ), 'admin.php' ) ) ); ?>">
				<?php _e( "What's New", 'edd_fes' ); ?>
			</a>
			<a class="nav-tab <?php echo $selected == 'fes-getting-started' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'fes-about', 'tab' => 'fes-getting-started' ), 'admin.php' ) ) ); ?>">
				<?php _e( 'Getting Started', 'edd_fes' ); ?>
			</a>
			<a class="nav-tab <?php echo $selected == 'fes-support' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'fes-about', 'tab' => 'fes-support' ), 'admin.php' ) ) ); ?>">
				<?php _e( 'Support & Documentation', 'edd_fes' ); ?>
			</a>
			<a class="nav-tab <?php echo $selected == 'fes-changelog' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'fes-about', 'tab' => 'fes-changelog' ), 'admin.php' ) ) ); ?>">
				<?php _e( 'Changelog', 'edd_fes' ); ?>
			</a>
		</h2>
		<?php
	}

	/**
	 * FES Welcome Screen Load Tab.
	 *
	 * Loads the correct page based
	 * on the tab selected on the FES
	 * welcome screen.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @return void
	 */
	public function load_page() { ?>
		<div class="wrap about-wrap">
			<?php
				$selected = isset( $_GET['tab'] ) ? $_GET['tab'] : 'fes-about';
				if ( $selected == 'fes-getting-started' ){
					return $this->getting_started_screen();
				} else if ( $selected == 'fes-support' ){
					return $this->support_screen();
				} else if ( $selected == 'fes-changelog' ){
					return $this->changelog_screen();
				} else {
					return $this->about_screen();
				}
			?>
		</div>
		<?php
	}

	/**
	 * FES Welcome Message.
	 *
	 * Displays the FES welcome page
	 * welcome message.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @return void
	 */
	public function welcome_message() {
		$version 	= explode('.', fes_plugin_version );
		$version 	= $version[0] . '.' . $version[1];
		$badge_url  = fes_assets_url . 'img/extensions2.jpg';
		?>
		<div id="fes-header">
			<div id="fes-header-top-row">
				<img class="fes-new-badge" src="<?php echo esc_url( $badge_url ); ?>" alt="Frontend Submissions" />
				<h1 class="fes-header-heading"><?php printf( __( 'Welcome to FES %s', 'edd_fes' ), $version ); ?></h1>
				<p class="about-text"><?php printf( _x( 'Thank you for updating to the latest version! Easy Digital Downloads Frontend Submissions %s is ready to make your online store faster, safer and better!', 'FES version number', 'edd_fes' ), $version ); ?></p>
			</div>
		</div>
		<?php
	}

	/**
	 * FES Welcome Page What's New Tab.
	 *
	 * Displays the What's New (about)
	 * tab content on the FES welcome
	 * page.
	 *
	 * @since 2.2.0
	 * @access public
	 *
	 * @return void
	 */
	public function about_screen() {

		// load welcome message and content tabs
		$version = explode('.', fes_plugin_version );
		$version = $version[0] . '.' . $version[1];
		$this->welcome_message();
		$this->tabs();
		?>
		<div class="fes-tab-content">
			<p class="about-description">
				<?php printf( _x( 'FES %s release highlights:', 'FES version number', 'edd_fes' ), $version ); ?>
			</p>

			<div class="fes-about-section">
				<h3><?php _e( 'All New Settings Interface', 'edd_fes');?></h3>
				<div class="feature-section">
					<div class="feature-section-media media-container">
						<img src="<?php echo fes_assets_url . 'img/fes-new-settings.png'; ?>" class="edd-welcome-screenshots"/>
					</div>
					<div class="feature-section-content">
						<h4><?php _e( 'Familiar Layout', 'edd_fes' ); ?></h4>
						<p><?php _e( 'FES 2.4 introduces a familiar settings interface. Previously, all FES settings were built into a framework that had to be integrated into WordPress. Now, all FES settings use core WordPress functionality making it more intuitive for those already familiar with WordPress.', 'edd_fes'); ?></p>
						<p><?php _e( 'Also, all settings have been reviewed to improve clarity and function, making it easier for you to understand exactly what tools you have for managing your marketplace.', 'edd_fes'); ?></p>
						<h4><?php _e( 'Easy Digital Downloads', 'edd_fes');?></h4>
						<p><?php _e( 'Regardless of its extensive functionality, FES is still an Easy Digital Downloads extension. It\'s only right that FES settings are coupled with EDD settings. The new Settings sub-menu link now leads to a dedicated FES settings tab on the EDD settings page.', 'edd_fes');?></p>
					</div>
				</div>
			</div>

<?php /* keep unneeded sections for future use

			<div class="fes-about-section">
				<h3><?php _e( 'Formbuilder Improvements', 'edd_fes');?></h3>
				<div class="feature-section">
					<div class="feature-section-media media-container">
						<img src="<?php echo fes_assets_url . 'img/show-on-download.png'; ?>" class="edd-welcome-screenshots"/>
					</div>
					<div class="feature-section-content">
						<h4><?php _e( 'New & Improved Fields','edd_fes');?></h4>
						<p><?php  _e( 'With FES 2.3, you can now add a honeypot field. In addition the reCAPTCHA field has been upgraded to use reCAPTCHA 2.0 and now with just a single click your vendors can confirm they are not a robot.', 'edd_fes');?></p>
						<h4><?php _e( 'Improved Sidebar', 'edd_fes');?></h4>
						<p><?php  _e( 'In FES 2.3, finding fields is easier than ever. Extension added fields and form-specific fields are now seperated from custom fields.', 'edd_fes');?></p>
						<h4><?php _e( 'Show Fields from the Submission on Product Pages', 'edd_fes');?></h4>
						<p><?php  _e( 'With FES 2.3, you can now show the values of all of your custom fields on the product pages for customers to see. Each custom field on the submission form now has a checkbox so you can pick and choose which ones to show.', 'edd_fes');?></p>
					</div>
				</div>
			</div>

			<div class="fes-about-section">
				<h3><?php _e( 'Settings Panel Upgrades', 'edd_fes');?></h3>
				<div class="feature-section">
					<div class="feature-section-media media-container">
						<img src="<?php echo fes_assets_url . 'img/fes-settings-screenshot.png'; ?>" class="edd-welcome-screenshots"/>
					</div>
					<div class="feature-section-content">
						<h4><?php _e( 'The Latest & Greatest from Redux Framework','edd_fes');?></h4>
						<p><?php _e( 'FES 2.3 upgrades the bundled version of the Redux Framework over 40 versions. The new bundled version of Redux Framework has been crafted with care to minimize distrations, and contains significant performance improvements over previous versions.', 'edd_fes');?></p>
						<h4><?php _e( 'Settings Panel Reorganization', 'edd_fes');?></h4>
						<p><?php  _e( 'The settings panel in FES has been reorganized to make setting up FES faster and easier than ever. Fields now conditionally hide depending on the values of other fields on-the-fly to minimize the number of fields you have to fill out.', 'edd_fes');?></p>
					</div>
				</div>
			</div>
*/ ?>

			<div class="fes-about-section">
				<div class="feature-section two-col">
					<div class="col">
						<div class="media-container">
							<img src="<?php echo fes_assets_url . 'img/fes-php-7.png'; ?>" alt="Frontend Submissions PHP 7" />
						</div>
						<h3><?php _e( 'PHP7 Compatibility', 'edd_fes');?></h3>
						<p><?php  _e( 'Maintaining software is not just about bug fixes and new features. Keeping up-to-date with associated technologies is a vital part of the process. FES 2.4 adds support for PHP7 allowing you to run your marketplace on updated server configurations.', 'edd_fes');?></p>
					</div>
					<div class="col">
						<div class="media-container">
							<img src="<?php echo fes_assets_url . 'img/fes-bug-fixes.png'; ?>" alt="Frontend Submissions" />
						</div>
						<h3><?php _e( 'Code Improvements', 'edd_fes');?></h3>
						<p><?php  _e( 'With so many moving parts, it\'s not uncommon to run into issues here and there. Each update is an effort to handle those issues before you even take notice. Over 75% of the changes in FES 2.4 are bug fixes and code improvements.', 'edd_fes');?></p>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * FES Welcome Page Getting Started Tab.
	 *
	 * Displays the Getting Started
	 * tab content on the FES welcome
	 * page.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @return void
	 */
	public function getting_started_screen() {

		// load welcome message and content tabs
		$this->welcome_message();
		$this->tabs();
		?>
		<div class="fes-tab-content">
			<p class="about-description">
				<?php _e( 'Use the tips below to get started using Frontend Submissions. You will be up and running in no time!', 'edd_fes'); ?>
			</p>

			<div class="fes-about-section">
				<h3><?php _e( 'Configure Your Marketplace Settings', 'edd_fes');?></h3>
				<div class="feature-section">
					<div class="feature-section-media media-container">
						<img src="<?php echo fes_assets_url . 'img/fes-new-settings.png'; ?>" class="edd-welcome-screenshots"/>
					</div>
					<div class="feature-section-content">
						<h4><?php printf( _x( '<a href="%s">EDD FES &rarr; Settings</a>', 'FES settings page link', 'edd_fes' ), admin_url( 'edit.php?post_type=download&page=edd-settings&tab=fes' ) ); ?></h4>
						<p><?php _e( 'FES makes it easy for vendors to submit products to your store without your assistance. However, you still need to control how the vendor interacts with your system.', 'edd_fes'); ?></p>
						<h4><?php _e( 'Settings & Emails', 'edd_fes');?></h4>
						<p><?php _e( 'Set your marketplace up to function exactly how you\'d like. Easily change the terms "vendor" and "product" to something that meets your needs or even customize the emails sent to you or your vendors every time a specific action is completed on your store.', 'edd_fes');?></p>
						<h4><?php _e( 'Permissions', 'edd_fes');?></h4>
						<p><?php _e( 'You can control permissions like whether or not vendors have access to your WordPress dashboard, if they will be able to edit their own products after submission, or if vendor registration is even allowed.', 'edd_fes' );?></p>
					</div>
				</div>
			</div>

			<div class="fes-about-section">
				<h3><?php _e( 'Build Your Forms With Precision', 'edd_fes' );?></h3>
				<div class="feature-section">
					<div class="feature-section-media media-container">
						<img src="<?php echo fes_assets_url . 'img/fes-form-builder-screenshot.png'; ?>" class="edd-welcome-screenshots"/>
					</div>
					<div class="feature-section-content">
						<h4><?php _e( 'Submission Form', 'edd_fes' ); ?></h4>
						<p><?php _e( 'Your FES Submission Form is what vendors will use to submit products to your site. Much like <em>you</em> would when creating a new download from your WordPress dashboard, vendors will use the Submission Form to create products from the front-end of your site.', 'edd_fes' ); ?></p>
						<p><?php _e( 'Using the powerful FES form builder interface, choose the exact form fields you\'d like your vendors to have access to when submitting products. You can provide everything from a list of available categories to repeatable file upload fields.', 'edd_fes');?></p>
						<h4><?php _e( 'Registration & Profile Form', 'edd_fes');?></h4>
						<p><?php _e( 'In order to become a vendor, a user must register using the FES registration form. You can customize the fields on the form to require certain information from your applicants. If approved, vendors can edit their own profiles based on the fields you\'ve built into the Profile Form.', 'edd_fes' );?></p>
					</div>
				</div>
			</div>

			<div class="fes-about-section">
				<h3><?php _e( 'Manage Your Marketplace Vendors', 'edd_fes' );?></h3>
				<div class="feature-section">
					<div class="feature-section-media media-container">
						<img src="<?php echo fes_assets_url . 'img/fes-vendor-screenshot.png'; ?>" class="edd-welcome-screenshots"/>
					</div>
					<div class="feature-section-content">
						<h4><?php printf( __( '<a href="%s">EDD FES &rarr; Vendors</a>', 'edd_fes' ), admin_url( 'admin.php?page=fes-vendors' ) ); ?></h4>
						<p><?php _e( 'Easily see information about all of your marketplace vendors in one convenient location. Quickly see information about a vendor like number of products sold or even total sales value.', 'edd_fes' ); ?></p>
						<p><?php _e( 'You can also use quick links to view, revoke, or suspend vendor accounts with ease.', 'edd_fes');?></p>
						<h4><?php _e( 'Vendor Management', 'edd_fes');?></h4>
						<p><?php _e( 'Without a doubt, vendors are the heartbeat of a marketplace system. That means vendor management and extremely important.', 'edd_fes' );?></p>
						<p><?php _e( 'Use FES\'s simple but powerful individual vendor screen to view or edit important information or even leave notes and view reports about a vendor\'s performance.', 'edd_fes' );?></p>
					</div>
				</div>
			</div>

			<div class="feature-section two-col">
				<div class="col">
					<h3><?php _e( 'Activate your license key.','edd_fes');?></h3>
					<p><?php printf( __( 'When you purchased FES, you were given a license key for activation in your WordPress dashboard. License keys are your connection to automatic updates and support. To activate your license key, visit the <a href="%s">EDD settings</a>.', 'edd_fes' ), admin_url( 'edit.php?post_type=download&page=edd-settings&tab=licenses' ) ); ?></p>
				</div>
				<div class="col">
					<h3><?php _e( 'Need more detailed instructions?','edd_fes');?></h3>
					<p><?php printf( __( 'Don\'t worry, we\'ve only scratched the surface. FES is highly customizable in both functionality and presentation. For more details on how to use FES or get help from support, visit the <a href="%s">Support & Documentation</a> page.', 'edd_fes' ), admin_url( 'admin.php?page=fes-about&tab=fes-support' ) ); ?></p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * FES Welcome Page Support & Documentation Tab.
	 *
	 * Displays the Support & Documentation
	 * tab content on the FES welcome
	 * page.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @return void
	 */
	public function support_screen() {

		// load welcome message and content tabs
		$this->welcome_message();
		$this->tabs();
		$fes_docs = 'http://docs.easydigitaldownloads.com/category/330-frontend-submissions';
		$edd_support = 'https://easydigitaldownloads.com/support/';
		$consultants = 'https://easydigitaldownloads.com/consultants/';
		$trello = 'https://trello.com/b/vT8aivfj/fes-development';
		?>
		<div class="fes-tab-content">
			<p class="about-description">
				<?php _e( 'We\'re here to help. Use FES\'s thorough documentation and amazing support as needed.', 'edd_fes'); ?>
			</p>

			<div class="feature-section two-col">
				<div class="col">
					<h3><?php _e( 'Highly Detailed Documentation','edd_fes');?></h3>
					<p><?php printf( __( 'With the number of features, integrations, and possibilities present within the FES ecosystem, it\'s expected that you will want to learn more about its capabilities. To do so, please visit the <a href="%s">FES documentation</a>.', 'edd_fes' ), esc_url( $fes_docs ) ); ?></p>
				</div>
				<div class="col">
					<h3><?php _e( 'Frontend Submissions Support','edd_fes');?></h3>
					<p><?php printf( __( 'FES is an extension for the Easy Digital Downloads plugin. Therefore, you can request support by submitting a ticket to the <a href="%s">Easy Digital Downloads support form</a>. You will need to have your license key handy.', 'edd_fes' ), $edd_support ); ?></p>
				</div>
				<div class="col">
					<h3><?php _e( 'Need Custom Work?','edd_fes');?></h3>
					<p><?php printf( __( 'While FES is capable of powering a complete marketplace system, you may need to tailor its functionality to your business with custom development. Easy Digital Downloads maintains a list of <a href="%s">recommended consultants</a> that may be able to assist you.', 'edd_fes' ), esc_url( $consultants ) ); ?></p>
				</div>
				<div class="col">
					<h3><?php _e( 'Official FES Roadmap','edd_fes');?></h3>
					<p><?php printf( __( 'FES is a very actively developed plugin. FES\'s official roadmap on <a href="%s">Trello</a> provides a glimpse at what we\'re working on for future versions.', 'edd_fes' ), esc_url( $trello ) ); ?></p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * FES Welcome Page Changelog Tab.
	 *
	 * Displays the Changelog
	 * tab content on the FES welcome
	 * page.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @return void
	 */
	public function changelog_screen() {

		// load welcome message and content tabs
		$this->welcome_message();
		$this->tabs();
		?>
		<div class="fes-tab-content">
			<div class="fes-changelog">
				<?php echo $this->parse_changelog(); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Parse the FES changelog.txt file.
	 *
	 * Retrieves and formats the FES
	 * changelog file for display on
	 * the changelog tab on the FES
	 * welcome page.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @return string HTML formatted changelog file
	 */
	public function parse_changelog() {
		$file = file_exists( fes_plugin_dir . 'changelog.txt' ) ? fes_plugin_dir . 'changelog.txt' : false;

		if ( ! $file ) {
			$changelog = '<p>' . __( 'No valid changelog was found.', 'edd_fes') . '</p>';
		} else {
			$changelog = file_get_contents( $file );
			$changelog = nl2br( esc_html( $changelog ) );
			$changelog = preg_replace( '/`(.*?)`/', '<code>\\1</code>', $changelog );
			$changelog = preg_replace( '/[\040]\*\*(.*?)\*\*/', ' <strong>\\1</strong>', $changelog );
			$changelog = preg_replace( '/[\040]\*(.*?)\*/', ' <em>\\1</em>', $changelog );
			$changelog = preg_replace( '/\*\s(.*?)\r/', '<li>\\0</li>', $changelog );
			$changelog = preg_replace( '/<li>\*\s/', '<li>', $changelog );
			$changelog = preg_replace( '/= (.*?) =/', '<h4 class="fes-changelog-title">\\1</h4>', $changelog );
			$changelog = preg_replace( '/\[(.*?)\]\((.*?)\)/', '<a href="\\2">\\1</a>', $changelog );
		}

		return $changelog;
	}

	/**
	 * Welcome page intercept.
	 *
	 * Detect a redirect intended for the
	 * welcome page and redirect the person
	 * to that page.
	 *
	 * @since 2.2.0
	 * @access public
	 *
	 * @return void
	 */
	public function welcome() {
		// Bail if no activation redirect
		if ( ! get_transient( '_fes_activation_redirect' ) ) {
			return;
		}

		// Delete the redirect transient
		delete_transient( '_fes_activation_redirect' );

		// Bail if activating from network, or bulk
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
			return;
		}

		wp_safe_redirect( admin_url( 'index.php?page=fes-about' ) ); exit;
	}
}