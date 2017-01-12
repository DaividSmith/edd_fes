<?php
/**
 * FES Tools
 *
 * This file deals with instantiating
 * all of FES's tools.
 *
 * @package FES
 * @subpackage Tools
 * @since 2.3.0
 *
 * @todo  Wow this file is exploding. We can
 *        probably condense the import/export
 *        form processing, as well as move them
 *        to their own file.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FES Tools.
 *
 * Deals with adding FES tools on the
 * Tools submenu page for FES.
 *
 * @since 2.3.0
 * @access public
 */
class FES_Tools {

	/**
	 * FES Tools Actions.
	 *
	 * Runs actions required to show
	 * and run the FES tools.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'fes_process_form_export' ) );
		add_action( 'admin_init', array( $this, 'fes_process_form_import' ) );
		add_action( 'admin_init', array( $this, 'fes_process_import_settings' ) );
		add_action( 'admin_init', array( $this, 'fes_process_form_reset' ) );
		add_action( 'admin_init', array( $this, 'fes_process_form_tools' ) );
		add_action( 'fes_tools_tab_forms', array( $this, 'fes_tools_tab_forms_display' ) );
	}

	/**
	 * FES tools panel
	 *
	 * Shows the tools panel which contains FES-specific tools including the
	 * built-in import/export system.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @return void
	 */
	function fes_tools_page() {
		wp_enqueue_style( 'dashboard' );
		wp_enqueue_script( 'dashboard' );
		$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'forms'; ?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2 class="nav-tab-wrapper">
				<?php
		foreach ( $this->fes_get_tools_tabs() as $tab_id => $tab_name ) {
			$tab_url = add_query_arg( array( 'tab' => $tab_id ) );
			$tab_url = remove_query_arg( array( 'edd-message' ), $tab_url );

			$active = $active_tab == $tab_id ? ' nav-tab-active' : '';
			echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab_name ) . '" class="nav-tab' . $active . '">' . esc_html( $tab_name ) . '</a>';
		} ?>
			</h2>
			<div class="metabox-holder">
				<?php do_action( 'fes_tools_tab_' . $active_tab ); ?>
			</div><!-- .metabox-holder -->
		</div><!-- .wrap -->
	<?php
	}

	/**
	 * Retrieve tools page tabs.
	 *
	 * Creates array of all of the tabs
	 * on the FES tools page.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @todo Error logs tab
	 * @todo Vendor activity tab
	 * @todo Rerun upgrade routine tab
	 *
	 * @return array Tabs of the tools page.
	 */
	function fes_get_tools_tabs() {
		$tabs                  = array();
		$tabs['forms']     = __( 'Form Tools', 'edd_fes' );

		/**
		 * FES Tools tabs.
		 *
		 * Allows for the addition of custom FES tools page
		 * tabs.
		 *
		 * @since 2.3.0
		 *
		 * @param  array $tabs FES tools page tabs.
		 */
		return apply_filters( 'fes_tools_tabs', $tabs );
	}

	/**
	 * FES tools page form tab
	 *
	 * Renders the form tab on
	 * the FES tools page.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @return void
	 */
	function fes_tools_tab_forms_display() {
		if ( ! EDD_FES()->vendors->user_is_admin() ) {
			return;
		}

		/**
		 * FES Tools before notices.
		 *
		 * Action that runs before FES outputs
		 * success/fail messages on the form tools
		 * page.
		 *
		 * @since 2.3.0
		 */
		do_action( 'fes_tools_forms_before' );

		if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'import' && isset( $_REQUEST['form'] ) ) {
			echo '<div class="updated"><p>'.__( 'Successfully imported the ', 'edd_fes' ) . $_REQUEST['form'] . __( ' form!' , 'edd_fes' ).'</p></div>';
		} else if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'reset' && isset( $_REQUEST['form'] ) ) {
			echo '<div class="updated"><p>'.__( 'Successfully reset the ', 'edd_fes' ) . $_REQUEST['form'] . __( ' form!' , 'edd_fes' ).'</p></div>';
		} else if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'delete' && isset( $_REQUEST['result'] ) ) {
			if ( $_REQUEST['action'] == 'success' && isset ( $_REQUEST['count'] ) ) {
				if ( $_REQUEST['count'] > 0 ) {
					echo '<div class="updated"><p>'.__( 'Successfully removed ', 'edd_fes' ) . $_REQUEST['count'] . __( ' extra form(s)!' , 'edd_fes' ).'</p></div>';
				}
				else {
					echo '<div class="updated"><p>'.__( 'No extra forms to remove!' , 'edd_fes' ).'</p></div>';
				}
			}
			else {
				echo '<div class="error"><p>'.__( 'No extra forms to remove!' , 'edd_fes' ).'</p></div>';
			}
		} else if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'reset_meta' && isset( $_REQUEST['result'] ) ) {
			if ( $_REQUEST['result'] == 'success' ) {
				echo '<div class="updated"><p>'.__( 'Reset meta for all forms!' , 'edd_fes' ).'</p></div>';

			}
			else {
				echo '<div class="error"><p>'.__( 'Resetting meta failed!' , 'edd_fes' ).'</p></div>';
			}
		} else if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'reset' && isset( $_REQUEST['result'] ) ) {
			if ( $_REQUEST['result'] == 'success' ) {
				echo '<div class="updated"><p>'.__( 'Reset all forms!' , 'edd_fes' ).'</p></div>';

			}
			else {
				echo '<div class="error"><p>'.__( 'Resetting forms failed!' , 'edd_fes' ).'</p></div>';
			}
		}  else if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'import_settings' && isset( $_REQUEST['result'] ) ) {
			if ( $_REQUEST['result'] == 'success' ) {
				echo '<div class="updated"><p>'.__( 'Imported old settings!' , 'edd_fes' ).'</p></div>';

			}
			else {
				echo '<div class="error"><p>'.__( 'Importing old settings failed!' , 'edd_fes' ).'</p></div>';
			}
		}
		?>
		<div class="metabox-holder meta-box-sortables ui-sortable">
			<div class="postbox">
				<div class="handlediv" title="Click to toggle"><br></div><h3 class="hndle"><span><?php _e( 'Import Form', 'edd_fes' ); ?></span></h3>
				<div class="inside">
					<p><?php _e( 'Import form settings from a .json file.', 'edd_fes' ); ?></p>
					<?php echo $this->form( 'import' ); ?>
				</div><!-- .inside -->
			</div><!-- .postbox -->
		</div><!-- .metabox-holder -->

		<div class="metabox-holder meta-box-sortables ui-sortable">
			<div class="postbox closed">
				<div class="handlediv" title="Click to toggle"><br></div><h3 class="hndle"><span><?php _e( 'Export Form', 'edd_fes' ); ?></span></h3>
				<div class="inside">
					<p><?php _e( 'Export form settings as a .json file', 'edd_fes' ); ?></p>
					<?php echo $this->form( 'export' ); ?>
				</div><!-- .inside -->
			</div><!-- .postbox -->
		</div><!-- .metabox-holder -->

		<div class="metabox-holder meta-box-sortables ui-sortable">
			<div class="postbox closed">
				<div class="handlediv" title="Click to toggle"><br></div><h3 class="hndle"><span><?php _e( 'Reset Form', 'edd_fes' ); ?></span></h3>
				<div class="inside">
					<p><?php _e( 'Restores form settings back to new install defaults', 'edd_fes' ); ?></p>
					<?php echo $this->form( 'reset' ); ?>
				</div><!-- .inside -->
			</div><!-- .postbox -->
		</div><!-- .metabox-holder -->

		<div class="metabox-holder meta-box-sortables ui-sortable">
			<div class="postbox closed">
				<div class="handlediv" title="Click to toggle"><br></div><h3 class="hndle"><span><?php _e( 'Reset All Forms', 'edd_fes' ); ?></span></h3>
				<div class="inside">
					<p><?php _e( 'This action resets all FES Forms', 'edd_fes' ); ?></p>
					<form method="post">
						<p><input type="hidden" name="fes_action" value="reset_forms" /></p>
						<p>
							<?php wp_nonce_field( 'reset_forms_nonce', 'reset_forms_nonce' ); ?>
							<?php submit_button( __( 'Reset Forms', 'edd_fes' ), 'secondary', 'submit', false ); ?>
						</p>
					</form>
				</div><!-- .inside -->
			</div><!-- .postbox -->
		</div><!-- .metabox-holder -->

		<div class="metabox-holder meta-box-sortables ui-sortable">
			<div class="postbox closed">
				<div class="handlediv" title="Click to toggle"><br></div><h3 class="hndle"><span><?php _e( 'Reset Meta for All Forms', 'edd_fes' ); ?></span></h3>
				<div class="inside">
					<p><?php _e( 'This action resets the meta added in FES 2.3 for all FES Forms', 'edd_fes' ); ?></p>
					<form method="post">
						<p><input type="hidden" name="fes_action" value="reset_meta_forms" /></p>
						<p>
							<?php wp_nonce_field( 'reset_meta_forms', 'reset_meta_forms' ); ?>
							<?php submit_button( __( 'Reset Meta For All Forms', 'edd_fes' ), 'secondary', 'submit', false ); ?>
						</p>
					</form>
				</div><!-- .inside -->
			</div><!-- .postbox -->
		</div><!-- .metabox-holder -->

		<div class="metabox-holder meta-box-sortables ui-sortable">
			<div class="postbox closed">
				<div class="handlediv" title="Click to toggle"><br></div><h3 class="hndle"><span><?php _e( 'Delete Extraneous Forms', 'edd_fes' ); ?></span></h3>
				<div class="inside">
					<p><?php _e( 'This action deletes all FES Forms which are not assigned to a form in the FES Settings Panel', 'edd_fes' ); ?></p>
					<form method="post" enctype="multipart/form-data" >
						<p><input type="hidden" name="fes_action" value="delete_extra_forms" /></p>
						<p>
							<?php wp_nonce_field( 'delete_extra_forms', 'delete_extra_forms' ); ?>
							<?php submit_button( __( 'Delete Unassigned Forms', 'edd_fes' ), 'secondary', 'submit', false ); ?>
						</p>
					</form>
				</div><!-- .inside -->
			</div><!-- .postbox -->
		</div><!-- .metabox-holder -->

		<div class="metabox-holder meta-box-sortables ui-sortable">
			<div class="postbox">
				<div class="handlediv" title="Click to toggle"><br></div><h3 class="hndle"><span><?php _e( 'Import  Pre-FES 2.4 Settings', 'edd_fes' ); ?></span></h3>
				<div class="inside">
					<p><?php _e( 'Import form settings from a pre-FES 2.4 .json file.', 'edd_fes' ); ?></p>
					<?php echo $this->form( 'import_settings' ); ?>
				</div><!-- .inside -->
			</div><!-- .postbox -->
		</div><!-- .metabox-holder -->

		<div class="metabox-holder meta-box-sortables ui-sortable">
			<div class="postbox">
				<div class="handlediv" title="Click to toggle"><br></div><h3 class="hndle"><span><?php _e( 'Export  Pre-FES 2.4 Settings', 'edd_fes' ); ?></span></h3>
				<div class="inside">
					<p><?php _e( 'Export form settings pre-FES 2.4 as a .json file.', 'edd_fes' ); ?></p>
					<?php echo $this->form( 'export_settings' ); ?>
				</div><!-- .inside -->
			</div><!-- .postbox -->
		</div><!-- .metabox-holder -->
	<?php
		/**
		 * FES tools page form tab below tools.
		 *
		 * Allow for content to be inserted below
		 * the form tools on the FES tools page.
		 *
		 * @since 2.3.0
		 */
		do_action( 'fes_tools_forms_after' );
	}

	/**
	 * FES form import.
	 *
	 * Imports a JSON text file of form settings.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @return void
	 */
	function fes_process_form_import() {

		if ( !isset( $_POST['fes_action'] ) || empty( $_POST['fes_action'] ) ) {
			return;
		}

		if ( ! EDD_FES()->vendors->user_is_admin() ) {
			return;
		}

		if ( $_POST['fes_action'] !== 'fes_import_form_settings' ){
			return;
		}

		if ( !wp_verify_nonce( $_POST['fes_import_form_settings'], 'fes_import_form_settings' ) ) {
			return;
		}


		$form = $_POST['fes_import_form'];
		switch ( $form ) {
			case 'registration':
				$extension = explode( '.', $_FILES['import_file']['name'] );
				$extension = end( $extension );

				if ( $extension != 'json' ) {
					wp_die( __( 'Please upload a valid .json file' ) );
				}

				$import_file = $_FILES['import_file']['tmp_name'];

				if ( empty( $import_file ) ) {
					wp_die( __( 'Please upload a file to import' ) );
				}

				// Retrieve the settings from the file and convert the json object to an array.
				$settings =  edd_object_to_array( json_decode( file_get_contents( $import_file ) ) );

				// Upgrade all fields
				foreach ( $settings as $id => $field ) {
					$field = fes_upgrade_field( $field );
					$settings[ $id ] = $field;
				}

				// if there's no form, let's make one
				if ( ! EDD_FES()->helper->get_option( 'fes-registration-form', false ) ) {
					$page_data = array(
						'post_status' => 'publish',
						'post_type' => 'fes-forms',
						'post_author' => get_current_user_id(),
						'post_title' => __( 'Registration Form', 'edd_fes' )
					);
					$page_id   = wp_insert_post( $page_data );
					update_post_meta( $page_id, 'fes-form', $settings );
					fes_save_initial_registration_form( $page_id, false );
				} else {
					update_post_meta( EDD_FES()->helper->get_option( 'fes-registration-form', false ), 'fes-form', $settings );
					fes_save_initial_registration_form( EDD_FES()->helper->get_option( 'fes-registration-form', false ), false );
				}

				wp_safe_redirect( admin_url( 'admin.php?page=fes-tools&action=import&form=registration&result=success&tab=forms' ) ); exit;
				break;
			case 'submission':
				$extension = explode( '.', $_FILES['import_file']['name'] );
				$extension = end( $extension );

				if ( $extension != 'json' ) {
					wp_die( __( 'Please upload a valid .json file' ) );
				}

				$import_file = $_FILES['import_file']['tmp_name'];

				if ( empty( $import_file ) ) {
					wp_die( __( 'Please upload a file to import' ) );
				}

				// Retrieve the settings from the file and convert the json object to an array.
				$settings =  edd_object_to_array( json_decode( file_get_contents( $import_file ) ) );

				// Upgrade all fields
				foreach ( $settings as $id => $field ) {
					$field = fes_upgrade_field( $field );
					$settings[ $id ] = $field;
				}

				// if there's no form, let's make one
				if ( ! EDD_FES()->helper->get_option( 'fes-submission-form', false ) ) {
					$page_data = array(
						'post_status' => 'publish',
						'post_type' => 'fes-forms',
						'post_author' => get_current_user_id(),
						'post_title' => __( 'Submission Form', 'edd_fes' )
					);
					$page_id   = wp_insert_post( $page_data );
					update_post_meta( $page_id, 'fes-form', $settings );
					fes_save_initial_submission_form( $page_id, false );
				} else {
					update_post_meta( EDD_FES()->helper->get_option( 'fes-submission-form', false ), 'fes-form', $settings );
					fes_save_initial_submission_form( EDD_FES()->helper->get_option( 'fes-submission-form', false ), false );
				}

				wp_safe_redirect( admin_url( 'admin.php?page=fes-tools&action=import&form=submission&result=success&tab=forms' ) ); exit;
				break;
			case 'profile':
				$extension = explode( '.', $_FILES['import_file']['name'] );
				$extension = end( $extension );

				if ( $extension != 'json' ) {
					wp_die( __( 'Please upload a valid .json file' ) );
				}

				$import_file = $_FILES['import_file']['tmp_name'];

				if ( empty( $import_file ) ) {
					wp_die( __( 'Please upload a file to import' ) );
				}

				// Retrieve the settings from the file and convert the json object to an array.
				$settings =  edd_object_to_array( json_decode( file_get_contents( $import_file ) ) );

				// Upgrade all fields & remove user login field from the profile form
				if ( isset( $this->new_settings['fes-profile-form'] ) && $this->new_settings['fes-profile-form'] != '' ) {
					$old_fields = get_post_meta( $this->new_settings['fes-profile-form'], 'fes-form', true );
					$count = 0;
					if ( is_array( $old_fields ) ) {
						foreach ( $old_fields as $id => $field ) {
							if ( isset( $field['template'] ) && $field['template'] === 'user_login' ){
								continue;
							}

							$old_fields[ $count ] = fes_upgrade_field( $field );; // save new field back
							$count++;
						}
						update_post_meta( $this->new_settings['fes-profile-form'], 'fes-form', $old_fields );
					}
				}

				// if there's no form, let's make one
				if ( ! EDD_FES()->helper->get_option( 'fes-profile-form', false ) ) {
					$page_data = array(
						'post_status' => 'publish',
						'post_type' => 'fes-forms',
						'post_author' => get_current_user_id(),
						'post_title' => __( 'Profile Form', 'edd_fes' )
					);
					$page_id   = wp_insert_post( $page_data );
					update_post_meta( $page_id, 'fes-form', $settings );
					fes_save_initial_profile_form( $page_id, false );
				} else {
					update_post_meta( EDD_FES()->helper->get_option( 'fes-profile-form', false ), 'fes-form', $settings );
					fes_save_initial_profile_form( EDD_FES()->helper->get_option( 'fes-profile-form', false ), false );
				}

				wp_safe_redirect( admin_url( 'admin.php?page=fes-tools&action=import&form=profile&result=success&tab=forms' ) ); exit;
				break;
			}
	}

	/**
	 * FES settings import.
	 *
	 * Imports a JSON text file of FES settings.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @deprecated 2.4.0 Use EDD's system instead.
	 *
	 * @return void
	 */
	function fes_process_import_settings() {

		if ( !isset( $_POST['fes_action'] ) || empty( $_POST['fes_action'] ) ) {
			return;
		}

		if ( ! EDD_FES()->vendors->user_is_admin() ) {
			return;
		}

		if ( $_POST['fes_action'] !== 'fes_import_settings' ){
			return;
		}

		if ( !wp_verify_nonce( $_POST['fes_import_settings'], 'fes_import_settings' ) ) {
			return;
		}

		$extension = explode( '.', $_FILES['import_file']['name'] );
		$extension = end( $extension );

		if ( $extension != 'json' ) {
			wp_die( __( 'Please upload a valid .json file' ) );
		}

		$import_file = $_FILES['import_file']['tmp_name'];

		if ( empty( $import_file ) ) {
			wp_die( __( 'Please upload a file to import' ) );
		}

		// Retrieve the settings from the file and convert the json object to an array.
		$settings =  edd_object_to_array( json_decode( file_get_contents( $import_file ) ) );

		// Import all settings
		foreach ( $settings as $key => $value ) {
			if ( substr( $key, 0, 3 ) === "fes" ) {
				edd_update_option( $key, $value );
				global $fes_settings, $edd_options;
				$fes_settings = $edd_options;
			}
		}
		wp_safe_redirect( admin_url( 'admin.php?page=fes-tools&action=import_settings&result=success&tab=forms' ) ); exit;
	}

	/**
	 * FES settings export.
	 *
	 * Export FES < 2.4 settings (useful
	 * if your settings didn't import correctly).
	 *
	 * @since 2.4.0
	 * @access public
	 *
	 * @deprecated 2.4.0 Only use for archival purposes.
	 *
	 * @return void
	 */
	function fes_process_export_settings() {

		if ( !isset( $_POST['fes_action'] ) || empty( $_POST['fes_action'] ) ) {
			return;
		}

		if ( ! EDD_FES()->vendors->user_is_admin() ) {
			return;
		}

		if ( $_POST['fes_action'] !== 'fes_export_settings' ){
			return;
		}

		if ( !wp_verify_nonce( $_POST['fes_export_settings'], 'fes_export_settings' ) ) {
			return;
		}

		$settings = get_option( 'fes_settings' );
		ignore_user_abort( true );

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=fes-settings-export-' . date( 'm-d-Y' ) . '.json' );
		header( "Expires: 0" );

		echo json_encode( $settings );
		exit;
	}

	/**
	 * FES form reset.
	 *
	 * Reset an FES form.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @return void
	 */
	function fes_process_form_reset() {
		if ( !isset( $_POST['fes_action'] ) || empty( $_POST['fes_action'] ) ) {
			return;
		}

		if ( ! EDD_FES()->vendors->user_is_admin() ) {
			return;
		}

		if ( $_POST['fes_action'] !== 'fes_reset_form_settings' ){
			return;
		}

		if ( !wp_verify_nonce( $_POST['fes_reset_form_settings'], 'fes_reset_form_settings' ) ) {
			return;
		}

		$form = $_POST['fes_reset_form'];
		switch ( $form ) {
			case 'login':
				// if there's no form, let's make one
				if ( ! EDD_FES()->helper->get_option( 'fes-login-form', false ) ) {
					$page_data = array(
						'post_status' => 'publish',
						'post_type' => 'fes-forms',
						'post_author' => get_current_user_id(),
						'post_title' => __( 'Login Form', 'edd_fes' )
					);
					$page_id   = wp_insert_post( $page_data );
					fes_save_initial_login_form( $page_id );
					EDD_FES()->helper->set_option( 'fes-login-form', $page_id );
				} else {
					fes_save_initial_login_form( EDD_FES()->helper->get_option( 'fes-login-form', false ) );
				}

				wp_safe_redirect( admin_url( 'admin.php?page=fes-tools&action=reset&form=login&result=success&tab=forms' ) ); exit;
				break;
			case 'registration':
				// if there's no form, let's make one
				if ( ! EDD_FES()->helper->get_option( 'fes-registration-form', false ) ) {
					$page_data = array(
						'post_status' => 'publish',
						'post_type' => 'fes-forms',
						'post_author' => get_current_user_id(),
						'post_title' => __( 'Registration Form', 'edd_fes' )
					);
					$page_id   = wp_insert_post( $page_data );
					fes_save_initial_registration_form( $page_id );
					EDD_FES()->helper->set_option( 'fes-registration-form', $page_id );
				} else {
					fes_save_initial_registration_form( EDD_FES()->helper->get_option( 'fes-registration-form', false ) );
				}

				wp_safe_redirect( admin_url( 'admin.php?page=fes-tools&action=reset&form=registration&result=success&tab=forms' ) ); exit;
				break;
			case 'submission':
				// if there's no form, let's make one
				if ( ! EDD_FES()->helper->get_option( 'fes-submission-form', false ) ) {
					$page_data = array(
						'post_status' => 'publish',
						'post_type' => 'fes-forms',
						'post_author' => get_current_user_id(),
						'post_title' => __( 'Submission Form', 'edd_fes' )
					);
					$page_id   = wp_insert_post( $page_data );
					fes_save_initial_submission_form( $page_id );
					EDD_FES()->helper->set_option( 'fes-submission-form', $page_id );
				} else {
					fes_save_initial_submission_form( EDD_FES()->helper->get_option( 'fes-submission-form', false ) );
				}

				wp_safe_redirect( admin_url( 'admin.php?page=fes-tools&action=reset&form=submission&result=success&tab=forms' ) ); exit;
				break;
			case 'profile':
				// if there's no form, let's make one
				if ( ! EDD_FES()->helper->get_option( 'fes-profile-form', false ) ) {
					$page_data = array(
						'post_status' => 'publish',
						'post_type' => 'fes-forms',
						'post_author' => get_current_user_id(),
						'post_title' => __( 'Profile Form', 'edd_fes' )
					);
					$page_id   = wp_insert_post( $page_data );
					fes_save_initial_profile_form( $page_id );
					EDD_FES()->helper->set_option( 'fes-profile-form', $page_id );
				} else {
					fes_save_initial_profile_form( EDD_FES()->helper->get_option( 'fes-profile-form', false ) );
				}

				wp_safe_redirect( admin_url( 'admin.php?page=fes-tools&action=reset&form=profile&result=success&tab=forms' ) ); exit;
				break;
			case 'vendor_contact':
				// if there's no form, let's make one
				if ( ! EDD_FES()->helper->get_option( 'fes-vendor-contact-form', false ) ) {
					$page_data = array(
						'post_status' => 'publish',
						'post_type' => 'fes-forms',
						'post_author' => get_current_user_id(),
						'post_title' => __( 'Contact Form', 'edd_fes' )
					);
					$page_id   = wp_insert_post( $page_data );
					fes_save_initial_vendor_contact_form( $page_id );
					EDD_FES()->helper->set_option( 'fes-vendor-contact-form', $page_id );
				} else {
					fes_save_initial_vendor_contact_form( EDD_FES()->helper->get_option( 'fes-vendor-contact-form', false ) );
				}

				wp_safe_redirect( admin_url( 'admin.php?page=fes-tools&action=reset&form=contact&result=success&tab=forms' ) ); exit;
				break;
		}
	}

	/**
	 * FES form export.
	 *
	 * Export an FES form.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @return void
	 */
	function fes_process_form_export() {

		if ( !isset( $_POST['fes_action'] ) || empty( $_POST['fes_action'] ) ) {
			return;
		}

		if ( ! EDD_FES()->vendors->user_is_admin() ) {
			return;
		}

		if ( $_POST['fes_action'] !== 'fes_export_form_settings' ){
			return;
		}

		if ( !wp_verify_nonce( $_POST['fes_export_form_settings'], 'fes_export_form_settings' ) ) {
			return;
		}

		$form = $_POST['fes_export_form'];
		switch ( $form ) {
			case 'registration':
				if ( !EDD_FES()->helper->get_option( 'fes-registration-form', false ) ) {
					return;
				}
				$settings = get_post_meta( EDD_FES()->helper->get_option( 'fes-registration-form', false ) , 'fes-form', false );
				ignore_user_abort( true );
				nocache_headers();
				header( 'Content-Type: application/json; charset=utf-8' );
				header( 'Content-Disposition: attachment; filename=fes-registration-form-export-' . date( 'm-d-Y' ) . '.json' );
				header( "Expires: 0" );

				echo json_encode( $settings ) ;
				exit;
				break;
			case 'submission':
				if ( !EDD_FES()->helper->get_option( 'fes-submission-form', false ) ) {
					return;
				}
				$settings = get_post_meta( EDD_FES()->helper->get_option( 'fes-submission-form', false ), 'fes-form', false );
				ignore_user_abort( true );

				nocache_headers();
				header( 'Content-Type: application/json; charset=utf-8' );
				header( 'Content-Disposition: attachment; filename=fes-submission-form-export-' . date( 'm-d-Y' ) . '.json' );
				header( "Expires: 0" );

				echo json_encode( $settings );
				exit;
				break;
			case 'profile':
				if ( !EDD_FES()->helper->get_option( 'fes-profile-form', false ) ) {
					return;
				}
				$settings = get_post_meta( EDD_FES()->helper->get_option( 'fes-profile-form', false ), 'fes-form', false );
				ignore_user_abort( true );

				nocache_headers();
				header( 'Content-Type: application/json; charset=utf-8' );
				header( 'Content-Disposition: attachment; filename=fes-profile-form-export-' . date( 'm-d-Y' ) . '.json' );
				header( "Expires: 0" );

				echo json_encode( $settings );
				exit;
				break;
		}
	}

	/**
	 * Process Form Tools.
	 *
	 * Process things like deleting extraneous
	 * forms and resetting all forms.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @return void
	 */
	function fes_process_form_tools() {

		if ( !isset( $_POST['fes_action'] ) || empty( $_POST['fes_action'] ) ) {
			return;
		}

		if ( ! EDD_FES()->vendors->user_is_admin() ) {
			return;
		}

		$form = $_POST['fes_action'];

		switch ( $form ) {
		case 'delete_extra_forms':
			if ( !wp_verify_nonce( $_POST['delete_extra_forms'], 'delete_extra_forms' ) ) {
				return;
			}
			$forms = get_posts( array( 'post_type' => 'fes-forms', 'fields' => 'ids', 'posts_per_page' => -1, '' ) );
			if ( !$forms ) {
				wp_safe_redirect( admin_url( 'admin.php?page=fes-tools&action=delete&result=fail&tab=forms' ) ); exit;
				break;
			}
			global $fes_settings;
			$count = 0;
			foreach ( $forms as $form ) {
				if ( isset( $fes_settings['fes-submission-form'] ) && $fes_settings['fes-submission-form'] == $form && $fes_settings['fes-submission-form'] ) {
					continue;
				} else if ( isset( $fes_settings['fes-profile-form'] ) && $fes_settings['fes-profile-form'] == $form && $fes_settings['fes-profile-form'] ) {
						continue;
					} else if ( isset( $fes_settings['fes-registration-form'] ) && $fes_settings['fes-registration-form'] == $form && $fes_settings['fes-registration-form'] ) {
						continue;
					} else if ( isset( $fes_settings['fes-login-form'] ) && $fes_settings['fes-login-form'] == $form && $fes_settings['fes-login-form'] ) {
						continue;
					} else if ( isset( $fes_settings['fes-vendor-contact-form'] ) && $fes_settings['fes-vendor-contact-form'] == $form && $fes_settings['fes-vendor-contact-form'] ) {
						continue;
					} else {
					wp_delete_post( $form, true ); // not assigned so delete it
					$count++;
				}
			}
			wp_safe_redirect( admin_url( 'admin.php?page=fes-tools&action=delete&count='.$count.'&result=success&tab=forms&tab=forms' ) ); exit;
			break;
		case 'reset_forms':
			if ( !wp_verify_nonce( $_POST['reset_forms_nonce'], 'reset_forms_nonce' ) ) {
				return;
			}
			$forms = get_posts( array( 'post_type' => 'fes-forms', 'fields' => 'ids', 'posts_per_page' => -1 ) );
			if ( $forms && !empty( $forms ) ) {
				foreach ( $forms as $form => $id ){
					wp_delete_post( $id, true );
				}
			}

			$page_data = array(
				'post_status' => 'publish',
				'post_type' => 'fes-forms',
				'post_author' => get_current_user_id(),
				'post_title' => __( 'Login Form', 'edd_fes' )
			);

			$page_id   = wp_insert_post( $page_data );
			fes_save_initial_login_form( $page_id );
			EDD_FES()->helper->set_option( 'fes-login-form', $page_id );


			$page_data = array(
				'post_status' => 'publish',
				'post_type' => 'fes-forms',
				'post_author' => get_current_user_id(),
				'post_title' => __( 'Registration Form', 'edd_fes' )
			);

			$page_id   = wp_insert_post( $page_data );
			fes_save_initial_registration_form( $page_id );
			EDD_FES()->helper->set_option( 'fes-registration-form', $page_id );

			$page_data = array(
				'post_status' => 'publish',
				'post_type' => 'fes-forms',
				'post_author' => get_current_user_id(),
				'post_title' => __( 'Submission Form', 'edd_fes' )
			);
			$page_id   = wp_insert_post( $page_data );
			fes_save_initial_submission_form( $page_id );
			EDD_FES()->helper->set_option( 'fes-submission-form', $page_id );

			$page_data = array(
				'post_status' => 'publish',
				'post_type' => 'fes-forms',
				'post_author' => get_current_user_id(),
				'post_title' => __( 'Profile Form', 'edd_fes' )
			);
			$page_id   = wp_insert_post( $page_data );
			fes_save_initial_profile_form( $page_id );
			EDD_FES()->helper->set_option( 'fes-profile-form', $page_id );

			$page_data = array(
				'post_status' => 'publish',
				'post_type' => 'fes-forms',
				'post_author' => get_current_user_id(),
				'post_title' => __( 'Contact Form', 'edd_fes' )
			);
			$page_id   = wp_insert_post( $page_data );
			fes_save_initial_vendor_contact_form( $page_id );
			EDD_FES()->helper->set_option( 'fes-vendor-contact-form', $page_id );

			wp_safe_redirect( admin_url( 'admin.php?page=fes-tools&action=reset&result=success&tab=forms' ) ); exit;
			break;
		case 'reset_meta_forms':
			if ( !wp_verify_nonce( $_POST['reset_meta_forms'], 'reset_meta_forms' ) ) {
				return;
			}
			$forms = get_posts( array( 'post_type' => 'fes-forms', 'fields' => 'ids', 'posts_per_page' => -1, '' ) );
			if ( !$forms ) {
				wp_safe_redirect( admin_url( 'admin.php?page=fes-tools&action=reset_meta&result=fail&tab=forms' ) ); exit;
				break;
			}
			global $fes_settings;
			foreach ( $forms as $form ) {
				if ( isset( $fes_settings['fes-submission-form'] ) && $fes_settings['fes-submission-form'] == $form && $fes_settings['fes-submission-form'] ) {
					fes_save_initial_submission_form( $form, false );
					continue;
				} else if ( isset( $fes_settings['fes-profile-form'] ) && $fes_settings['fes-profile-form'] == $form && $fes_settings['fes-profile-form'] ) {
					fes_save_initial_profile_form( $form, false );
					continue;
				} else if ( isset( $fes_settings['fes-registration-form'] ) && $fes_settings['fes-registration-form'] == $form && $fes_settings['fes-registration-form'] ) {
					fes_save_initial_registration_form( $form, false );
					continue;
				} else if ( isset( $fes_settings['fes-login-form'] ) && $fes_settings['fes-login-form'] == $form && $fes_settings['fes-login-form'] ) {
					fes_save_initial_login_form( $form, false );
					continue;
				} else if ( isset( $fes_settings['fes-vendor-contact-form'] ) && $fes_settings['fes-vendor-contact-form'] == $form && $fes_settings['fes-vendor-contact-form'] ) {
					fes_save_initial_vendor_contact_form( $form, false );
					continue;
				}
			}
			wp_safe_redirect( admin_url( 'admin.php?page=fes-tools&action=reset_meta&result=success&tab=forms' ) ); exit;
			break;
		}
	}

	/**
	 * FES Tools page forms.
	 *
	 * Creates forms for use in
	 * the FES tools page metaboxes.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @deprecated 2.4.0
	 *
	 * @todo Discontinue this and just hardcode them in.
	 *
	 * @param string $type The type of dropdown.
	 * @return void
	 */
	public function form( $type = 'import' ) {
		if ( $type === 'import' ){
			$forms     			     = array();
			$forms['submission']     = __('Submission Form', 'edd_fes' );
			$forms['profile']        = __('Profile Form', 'edd_fes' );
			$forms['registration']   = __('Registration Form', 'edd_fes' );
			ob_start(); ?>
				<form method="post" enctype="multipart/form-data">
					<p>
						<input type="file" name="import_file"/>
					</p>
					<p>
						<input type="hidden" name="fes_action" value="fes_import_form_settings" />
						<?php $this->select( $forms, 'fes_import_form' ); ?>
						<?php wp_nonce_field( 'fes_import_form_settings', 'fes_import_form_settings' ); ?>
					</p>
					<p>
						<?php submit_button( __( 'Import', 'edd_fes' ), 'secondary', 'submit', false ); ?>
					</p>
				</form>
			<?php
			$output = ob_get_contents();
			ob_end_clean();
			return $output;
		} else if ( $type === 'export' ) {
			$forms     			     = array();
			$forms['submission']     = __('Submission Form', 'edd_fes' );
			$forms['profile']        = __('Profile Form', 'edd_fes' );
			$forms['registration']   = __('Registration Form', 'edd_fes' );
			ob_start(); ?>
				<form method="post" enctype="multipart/form-data">
					<p>
						<input type="hidden" name="fes_action" value="fes_export_form_settings" />
					</p>
					<p>
						<?php $this->select( $forms, 'fes_export_form' ); ?>
						<?php wp_nonce_field( 'fes_export_form_settings', 'fes_export_form_settings' ); ?>
					</p>
					<p>
						<?php submit_button( __( 'Export', 'edd_fes' ), 'secondary', 'submit', false ); ?>
					</p>
				</form>
			<?php
			$output = ob_get_contents();
			ob_end_clean();
			return $output;
		} else if ( $type === 'reset' ){
			$forms     			     = array();
			$forms['submission']     = __('Submission Form', 'edd_fes' );
			$forms['profile']        = __('Profile Form', 'edd_fes' );
			$forms['registration']   = __('Registration Form', 'edd_fes' );
			$forms['vendor_contact'] = __('Vendor Contact Form', 'edd_fes' );
			$forms['login']   		 = __('Login Form', 'edd_fes' );
			ob_start(); ?>
				<form method="post" enctype="multipart/form-data">
					<p>
						<input type="hidden" name="fes_action" value="fes_reset_form_settings" />
					</p>
					<p>
						<?php $this->select( $forms, 'fes_reset_form' ); ?>
						<?php wp_nonce_field( 'fes_reset_form_settings', 'fes_reset_form_settings' ); ?>
					</p>
					<p>
						<?php submit_button( __( 'Reset', 'edd_fes' ), 'secondary', 'submit', false ); ?>
					</p>
				</form>
			<?php
			$output = ob_get_contents();
			ob_end_clean();
			return $output;
		} else if ( $type === 'import_settings' ){
			ob_start(); ?>
				<form method="post" enctype="multipart/form-data">
					<p>
						<input type="file" name="import_file"/>
						<input type="hidden" name="fes_action" value="fes_import_settings" />
					</p>
					<p>
						<?php wp_nonce_field( 'fes_import_settings', 'fes_import_settings' ); ?>
					</p>
					<p>
						<?php submit_button( __( 'Reset', 'edd_fes' ), 'secondary', 'submit', false ); ?>
					</p>
				</form>
			<?php
			$output = ob_get_contents();
			ob_end_clean();
			return $output;
		} else if ( $type === 'export_settings' ){
			ob_start(); ?>
				<form method="post" enctype="multipart/form-data">
					<p>
						<input type="hidden" name="fes_action" value="fes_export_settings" />
					</p>
					<p>
						<?php wp_nonce_field( 'fes_export_form_settings', 'fes_export_form_settings' ); ?>
					</p>
					<p>
						<?php submit_button( __( 'Export', 'edd_fes' ), 'secondary', 'submit', false ); ?>
					</p>
				</form>
			<?php
			$output = ob_get_contents();
			ob_end_clean();
			return $output;
		} else {
			return '';
		}
	}

	/**
	 * FES Tools page select.
	 *
	 * Creates chosen dropdown for use in
	 * the FES tools page metaboxes.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @deprecated 2.4.0
	 *
	 * @todo Discontinue this and just hardcode them in.
	 * @todo Use Select2 when WordPress or EDD adds it to core.
	 *
	 * @param array $forms Array of FES forms to include in dropdown.
	 * @param string $class CSS class to use in dropdown.
	 * @return void
	 */
	public function select( $forms, $class ){
		$placeholder = 'submission';
		$output = "<select style='width:200px;' name='$class' id='$class' class='chosen' data-placeholder='$placeholder'>\n";
		foreach ( $forms as $id => $name ) {
			$select = selected( $id, 'submission', false );
			$output .= "\t<option value='$id' $select>$name</option>\n";
		}
		$output .= "</select>";

		$output .= '<script type="text/javascript">jQuery(function() {jQuery(".chosen").chosen({width: "200px"});});</script>';
		echo $output;
	}
}
