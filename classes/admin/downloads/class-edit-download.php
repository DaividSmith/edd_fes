<?php
/**
 * Edit Download Screen
 *
 * Runs actions and filters on the edit download 
 * screen.
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
 * FES Edit Download
 *
 * Enhances the admin edit download screen
 * as well as adds FES's custom field metabox
 *
 * @since 2.0.0
 * @access public
 */
class FES_Edit_Download {

	/**
	 * Registers all actions and filters for the edit download screen.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @return void
	 */
	function __construct() {
		add_action( 'wp_dropdown_users', array( $this, 'author_vendor_roles' ), 0, 1 );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_meta' ), 11, 2 );
		add_action( 'add_meta_boxes', array( &$this, 'change_author_meta_box_title' ) );
	}

	/**
	 * Override the authors dropdown.
	 * 
	 * Changes the WordPress core author dropdown
	 * to a chosen download and also adds all vendors to
	 * the dropdown.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @uses FES_Edit_Download::vendor_selectbox Gets the HTML for the dropdown.
	 * @todo Convert to using Select2 instead of Chosen
	 * 
	 * @param string $output WordPress core HTML output for the dropdown.
	 * @return string The new HTML output for the dropdown.
	 */
	public function author_vendor_roles( $output ) {
		global $post;

		// if we can't garuntee what post we are on, exit immediately.
		if ( empty( $post ) || !is_object( $post ) || is_wp_error( $post ) ) {
			return $output;
		}

		// Return if this isn't an EDD download post type exit
		if ( $post->post_type != 'download' ) {
			return $output;
		}

		// Return if this isn't the vendor author override dropdown
		if ( !strpos( $output, 'post_author_override' ) ) {
			return $output;
		}

		// Default Chosen to the current post author
		$args = array(
			'selected' => $post->post_author,
			'id'       => 'post_author_override',
		);

		// Get the dropdown HTML
		$output = $this->vendor_selectbox( $args );

		return $output;
	}


	/**
	 * Vendor selectbox HTML.
	 * 
	 * Provides the HTML for the chosen dropdown for the author
	 * select on the admin edit download screen.
	 *
	 * @since 2.0.0
	 * @since 2.4.0 Prevent duplicates of same user in dropdown
	 * @since 2.4.0 No longer uses extract. Much saner way of using passed in args
	 * @access public
	 *
	 * @todo Convert to using Select2 instead of Chosen.
	 * 
	 * @param array $args {
	 *     Optional. An array of arguments for Chosen.
	 *
	 *     @type string $placeholder Placeholder for Chosen box to use.
	 *     @type string $id ID for Chosen box to use.
	 *     @type string $class Class for Chosen box to use.
	 * }
	 * @return string The new HTML output for the dropdown.
	 */
	public function vendor_selectbox( $args ) {
		$placeholder = ! empty( $args['placeholder'] ) ? esc_attr( $args['placeholder'] ) : '';
		$id 		 = ! empty( $args['id'] ) 		   ? esc_attr( $args['id'] ) 		  : '';
		$class 		 = ! empty( $args['class'] ) 	   ? esc_attr( $args['class'] )	      : '';
		$selected 	 = ! empty( $args['selected'] )    ? esc_attr( $args['selected'] )	  : '';
		
		// the roles for users that should appear in the dropdown
		$roles       = array( 'editor', 'suspended_vendor', 'frontend_vendor', 'administrator', 'contributor', 'shop_manager', 'shop_vendor', 'author' );

		/**
		 * Roles for the author dropdown.
		 *
		 * Filters the roles of users that should appear in the 
		 * author dropdown on the admin edit download screen.
		 *
		 * @since 2.4.0
		 *
		 * @param array $roles Roles to get all users of.
		 */		
		$roles     = apply_filters( 'fes_vendor_selectbox_roles', $roles );
		$user_args = array( 'fields' => array( 'ID', 'user_login' ) );

		$output    = "<select style='width:200px;' name='$id' id='$id' class='$class' data-placeholder='$placeholder'>\n";
		$output   .= "\t<option value=''></option>\n";

		if ( ! empty( $roles ) && is_array( $roles ) ) {
			$users = array();

			// for each role
			foreach ( $roles as $role ) {
				$new_args			= $user_args;
				$new_args['role']	= $role;

				// attempt to get the users that have the role
				$new_users = get_users( $new_args );

				// and merge them into the array of users we've found thus far (prevents duplicate names in the selectbox)
				$users = array_merge( $users, $new_users );
			}

			// if we've found users
			if ( ! empty( $users ) && is_array( $users ) ) {
				// for each user
				foreach ( (array) $users as $user ) {
					$select = selected( $user->ID, $selected, false );
					// add them as an option
					$output .= "\t<option value='$user->ID' $select>$user->user_login</option>\n";
				}
			}
		}

		$output .= "</select>";
		$output .= '<script type="text/javascript">jQuery(function() {jQuery("#' . $id . '").chosen();});</script>';
		return $output;
	}

	/**
	 * Vendor select metabox title.
	 * 
	 * Changes the title of the vendor select metabox from the
	 * WordPress default "Author" to the vendor constant.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @global $wp_meta_boxes Array of all metaboxes registered.
	 * 
	 * @return void
	 */
	public function change_author_meta_box_title() {
		global $wp_meta_boxes;
		$wp_meta_boxes['download']['normal']['core']['authordiv']['title'] = EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = true );
	}

	/**
	 * Download custom fields metabox registration.
	 * 
	 * Registers the metabox uses to output custom fields
	 * on the admin edit download screen.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @return void
	 */
	public function add_meta_boxes() {
		add_meta_box( 'fes-custom-fields', __( 'Frontend Submissions', 'edd_fes' ) , array( $this, 'render_form' ) , 'download', 'normal', 'high' );
	}

	/**
	 * Download custom fields metabox HTML.
	 * 
	 * Makes the form for the custom fields metabox
	 * FES adds to the edit download screen.
	 *
	 * @since 2.0.0
	 * @since 2.3.0 Uses the Forms API to render now.
	 * @access public
	 *
	 * @global $post The current download, as provided by WP core.
	 * @uses  FES_Forms::render_form_admin() Provides the HTML for the form.
	 *
	 * @param string $type Type of FES form.
	 * @param int $id ID of user/post to edit in form.
	 * @param bool $readonly Whether the form is readonly.
	 * @param array $args Additional arguments to send 
	 *                    to form rendering functions.
	 * @return void
	 */
	public function render_form( $type = 'submission', $id = false, $read_only = false, $args = array() ) {
		global $post;

		// if we can't garuntee what post we are on, exit immediately.
		if ( empty( $post ) || !is_object( $post ) || is_wp_error( $post ) ) {
			return '';
		}

		// Return if this isn't an EDD download post type exit
		if ( $post->post_type != 'download' ) {
			return '';
		}

		// attempt to get the form id of the submission form
		$form_id = EDD_FES()->helper->get_option( 'fes-submission-form', false );

		// if we can't find the submission form, echo an error
		if ( !$form_id ) {
			return _e( 'Submission form not set in FES settings' , 'edd_fes' );
		}

		$form = EDD_FES()->helper->get_form_by_id( $form_id, $post->ID );
		$author = $post->post_author;
		
		/* If there's no author we're on a auto-save prior to a draft being saved
		 * or alternatively a detached author download where a download was created,
		 * the user who created the download was deleted and the user's contents were
		 * not re-assigned to another author */ 
		if ( $author ) {
			// if we have an author, let's see if they are an admin
			$db_user = new FES_DB_Vendors();
			if ( $db_user->exists( 'user_id', $author ) ) {
				$vendor = new FES_Vendor( (int) $author, true );
				$vid = $vendor->id; 
				// and if they are a vendor, let's output a link to their FES admin vendor profile
				?>
				<a href="<?php echo admin_url( "admin.php?page=fes-vendors&view=overview&id=". $vid ."" ); ?>"><?php echo __( 'View', 'edd_fes' ) .  ' ' . EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = false ) . ' ' . __( 'edit page', 'edd_fes' );?></a><br /><br />
				<?php
			}
		}
		// let's output the FES Form
		echo $form->render_form_admin();
	}

	/**
	 * Save custom fields on Edit Download screen.
	 * 
	 * Saves the custom fields FES outputs in it's custom
	 * metabox on the edit download screen in the admin.
	 *
	 * @since 2.0.0
	 * @since 2.3.0 Uses the Forms API to save now
	 * @access public
	 *
	 * @uses  FES_Forms::save_form() Provides the HTML for the form.
	 *
	 * @param  int $post_id The post id of the download.
	 * @param  WP_Post $post A post object of the currently edited download.
	 * @return void
	 */
	public function save_meta( $post_id, $post ) {
		// if we're not on a download post item, exit immediately.
		if ( isset( $post->post_type ) && $post->post_type !== 'download' ) {
			return;
		}

		/* if the save_post action has been called by WordPress doing an autosave
		 * or if save_post has been called a bulk edit call, exit immediately */
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) {
			return;
		}

		// if the current user can't edit this product, abort.
		if ( ! current_user_can( 'edit_product', $post_id ) ) {
			return;
		}

		// Is the user allowed to edit the post or page?
		$form_id = EDD_FES()->helper->get_option( 'fes-submission-form', false );

		/* It's possible for a user to have edit_product but not edit_post for 
		 * particular download. While rare, this can be caused by a role scoping plugin
		 * or by locking down edit post */
		if ( current_user_can( 'edit_post', $post_id ) && $form_id ) {
			// Make the FES Form object.
			$form = new FES_Submission_Form( $form_id, 'id', $post_id );
			// Save the FES Form
			$form->save_form( $_POST, get_current_user_id() );
		}
	}
}