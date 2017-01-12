<?php
/**
 * FES Formbuilder
 *
 * Creates the formbuilder display and
 * also contains the save routine.
 *
 * @package FES
 * @subpackage Formbuilder
 * @since 2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { 
	exit;
}

/**
 * Formbuilder Updated Message.
 * 
 * Shows the updated message when the formbuilder is saved.
 *
 * @since 2.0.0
 * @access public
 * 
 * @param array $messages Messages by update response code.
 * @return array Messages by update response code.
 */
function fes_forms_form_updated_message( $messages ) {
	$message = array(
		0  => '',
		1  => __( 'Form updated.', 'edd_fes' ) ,
		2  => __( 'Form updated.', 'edd_fes' ) ,
		3  => __( 'Form updated.', 'edd_fes' ) ,
		4  => __( 'Form updated.', 'edd_fes' ) ,
		5  => isset( $_GET['revision'] ) ? sprintf( __( 'Form restored to revision from %s', 'edd_fes' ) , wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6  => __( 'Form updated.', 'edd_fes' ) ,
		7  => __( 'Form updated.', 'edd_fes' ) ,
		8  => __( 'Form updated.', 'edd_fes' ) ,
		9  => __( 'Form updated.', 'edd_fes' ) ,
		10 => __( 'Form updated.', 'edd_fes' )
	);

	/**
	 * Messages for the FES formbuilder
	 *
	 * Lets you edit the FES messages via a filter rather than
	 * via a translation.
	 *
	 * @since 2.0.0
	 *
	 * @param array $message Array of update code to message for the formbuilder.
	 */	
	$messages['fes-forms'] = apply_filters( 'fes_forms_form_updated_message', $message );
	return $messages;
}
add_filter( 'post_updated_messages', 'fes_forms_form_updated_message' );

/**
 * Formbuilder Metaboxes.
 * 
 * Registers the metaboxes being shown on the formbuilder.
 *
 * @since 2.0.0
 * @access public
 * 
 * @return void
 */
function fes_forms_add_meta_boxes() {
	$tab = isset( $_REQUEST['tab'] ) ? isset( $_REQUEST['tab'] ) : 'fields';
	$id = get_the_ID();
	if ( empty( $id ) ){
		return;
	}

	if ( $tab === 'fields' ) {
		add_meta_box( 'fes-metabox-editor', __( 'Form Editor', 'edd_fes' ), 'fes_formbuilder_fields_metabox', 'fes-forms', 'normal', 'high' );
		add_meta_box( 'fes-metabox-save'  , __( 'Save', 'edd_fes' ), 'fes_formbuilder_save', 'fes-forms', 'side', 'core' );
		$fname = '';
		foreach ( EDD_FES()->load_forms as $name => $class ) {
			$form = new $class( $name, 'name' );
			if ( $form->id == $id ) {
				$fname = $form->title();
				break;
			}
		}
		$metabox_title = sprintf( __( 'Add %s Form Fields', 'edd_fes' ), $fname );
		add_meta_box( 'fes-metabox-fields-specific', $metabox_title, 'fes_formbuilder_sidebar_specfic', 'fes-forms', 'side'  , 'core' );
		add_meta_box( 'fes-metabox-fields-custom', __( 'Add Custom Fields', 'edd_fes' ), 'fes_formbuilder_sidebar_custom', 'fes-forms', 'side'  , 'core' );
		add_meta_box( 'fes-metabox-fields-extension', __( 'Add Extension Created Fields', 'edd_fes' ), 'fes_formbuilder_sidebar_extension', 'fes-forms', 'side', 'core' );
		remove_meta_box( 'submitdiv', 'fes-forms', 'side' );
		remove_meta_box( 'slugdiv', 'fes-forms', 'normal' );

		/**
		 * Add custom metabox to field view formbuilder.
		 *
		 * Lets you add a metabox to the field tab of the formbuilder.
		 *
		 * @since 2.0.0
		 *
		 * @param int $id Id of the current form.
		 */	
		do_action( 'fes_add_custom_meta_boxes', $id );

	} else if ( $tab === 'settings' ) {
		add_meta_box( 'fes-metabox-editor', __( 'Form Settings', 'edd_fes' ) , 'fes_formbuilder_settings_metabox', 'fes-forms', 'normal', 'high' );
		add_meta_box( 'fes-metabox-save'  , __( 'Save', 'edd_fes' ), 'fes_formbuilder_save', 'fes-forms', 'side', 'core' );
	} else if ( $tab === 'notifications' ) {
		add_meta_box( 'fes-metabox-editor', __( 'Form Notifications', 'edd_fes' ), 'fes_formbuilder_notifications_metabox', 'fes-forms', 'normal', 'high' );
		add_meta_box( 'fes-metabox-save'  , __( 'Save', 'edd_fes' ), 'fes_formbuilder_save', 'fes-forms', 'side', 'core' );
	} else {
		//do_action( 'fes_register_custom_formbuilder_metaboxes', $tab ); // Do not use until FES 2.4
	}
}
add_action( 'add_meta_boxes_fes-forms', 'fes_forms_add_meta_boxes' );

// Kudos thomasgriffin
function fes_remove_all_the_metaboxes() {
	global $wp_meta_boxes;
	// This is the post type you want to target. Adjust it to match yours.
	$post_type  = 'fes-forms';
	// These are the metabox IDs you want to pass over. They don't have to match exactly. preg_match will be run on them.
	$pass_over  = array( 'fes-metabox-fields-specific', 'fes-metabox-fields-custom', 'fes-metabox-fields-extension','fes-metabox-editor','fes-metabox-save' );
	// All the metabox contexts you want to check.
	$contexts   = array( 'normal', 'advanced', 'side' );
	// All the priorities you want to check.
	$priorities = array( 'high', 'core', 'default', 'low' );
	// Loop through and target each context.
	foreach ( $contexts as $context ) {
		// Now loop through each priority and start the purging process.
		foreach ( $priorities as $priority ) {
			if ( isset( $wp_meta_boxes[$post_type][$context][$priority] ) ) {
				foreach ( (array) $wp_meta_boxes[$post_type][$context][$priority] as $id => $metabox_data ) {
					// If the metabox ID to pass over matches the ID given, remove it from the array and continue.
					if ( in_array( $id, $pass_over ) ) {
						unset( $pass_over[$id] );
						continue;
					}
					// Otherwise, loop through the pass_over IDs and if we have a match, continue.
					foreach ( $pass_over as $to_pass ) {
						if ( preg_match( '#^' . $id . '#i', $to_pass ) )
							continue;
					}
					// If we reach this point, remove the metabox completely.
					unset( $wp_meta_boxes[$post_type][$context][$priority][$id] );
				}
			}
		}
	}
}
add_action( 'add_meta_boxes', 'fes_remove_all_the_metaboxes', 100 );

/**
 * Formbuilder Fields Metabox
 * 
 * The content of the fields metabox in the formbuilder (contains 
 * all of the fields currently on a form).
 *
 * @since 2.0.0
 * @access public
 *
 * @global $pagenow Current page being viewed in admin.
 * @global $post Current post being viewed in admin.
 *
 * @param WP_Post $post Current post we're on (not used).
 * @return void
 */
function fes_formbuilder_fields_metabox( $post ) {
	global $post, $pagenow;

	$id = get_the_ID();
	if ( empty( $id ) ){
		return;
	}

	$fname = '';
	$lowerfname = '';
	foreach ( EDD_FES()->load_forms as $name => $class ) {
		$form = new $class( $name, 'name' );
		if ( $form->id == $id ) {
			$fname      = $form->title();
			$lowerfname = strtolower( $fname );
			break;
		}
	}

	$title = sprintf( __( 'FES %s Form Fields', 'edd_fes' ), $fname );

	/**
	 * Title of the fields metabox.
	 *
	 * Allows for the title of the fields metabox
	 * on the formbuilder to be adjusted.
	 *
	 * @since 2.0.0
	 *
	 * @param string $title Title of the current form.
	 * @param int $id Id of the current form.
	 */	
	$title = apply_filters( 'fes_' . $lowerfname . '_form_formbuilder_fields_metabox_title', $title, $id ); ?>
	<h1><?php echo $title; ?></h1>
	<div class="tab-content">
		<div id="fes-metabox" class="group">
		<?php
		$form = sprintf( __( 'Your %s form has no fields', 'edd_fes' ), $lowerfname );

		/**
		 * No fields on form wording.
		 *
		 * Allows for the wording of the fields metabox
		 * on the formbuilder to be adjusted when the
		 * form has no fields.
		 *
		 * @since 2.0.0
		 *
		 * @param string $form No fields message.
		 * @param int $id Id of the current form.
		 */	
		$form = apply_filters( 'fes_edit_form_area_no_fields', $form, $id );

		$form_inputs = get_post_meta( $post->ID, 'fes-form', true ) ;
		$form_inputs = isset( $form_inputs ) ? $form_inputs : array(); ?>

		<input type="hidden" name="fes-formbuilder-fields" id="fes-formbuilder-fields" value="<?php echo wp_create_nonce( "fes-formbuilder-fields" ); ?>" />
		<div style="margin-bottom: 10px">
			<button class="button fes-collapse"><?php _e( 'Toggle All Fields Open/Close', 'edd_fes' ); ?></button>
		</div>

		<?php
		if ( empty( $form_inputs ) ) { ?>
			<div class="fes-updated">
			  <p><?php echo $form; ?></p>
			</div>
			<?php } ?>

			<ul id="fes-formbuilder-fields" class="fes-formbuilder-fields unstyled">
				<?php
				if ( $form_inputs ) {
					$count = 0;
					foreach ( $form_inputs as $order => $input_field ) {
						if ( fes_is_key( $input_field['template'], EDD_FES()->load_fields ) ) {
							$class = EDD_FES()->load_fields[ $input_field['template'] ];
							$name  = isset( $input_field['name'] ) ? trim( $input_field['name'] ) : '';
							$class = new $class( $name, $id );
							echo $class->render_formbuilder_field( $count, false );
						} else {
							_fes_deprecated_function( 'Inserting a custom field without using FES Fields API', '2.3' );

							/**
							 * Show the formbuilder field of a pre-2.3 field.
							 *
							 * For fields made prior to the introduction of the
							 * FES field class, this action allows for those 
							 * extensions to output their formbuilder field.
							 *
							 * @since 2.0.0
							 *
							 * @deprecated 2.3.0
							 * @see  FES_Field
							 * 
							 * @param int $count The order of the field in the form.
							 * @param array $input_field The field's stored characteristics.
							 */								
							do_action( 'fes_admin_field_' . $input_field['template'], $count, $input_field );
						}
						$count++;
					}
				} ?>
			</ul>
		</div>
	</div>
	<?php
}

/**
 * Formbuilder Settings Metabox.
 * 
 * The content of the settings metabox in the formbuilder (contains 
 * settings for a form).
 *
 * @since 2.4.0
 * @access public
 *
 * @param WP_post $post WP_Post object of FES form.
 * @return void
 */
function fes_formbuilder_settings_metabox( $post ) {
	// Not in use until 2.4.0
}

/**
 * Formbuilder Notifications Metabox.
 * 
 * The content of the notifications metabox in the formbuilder (contains 
 * notifications for a form).
 *
 * @since 2.4.0
 * @access public
 *
 * @param WP_post $post WP_Post object of FES form.
 * @return void
 */
function fes_formbuilder_notifications_metabox( $post ) {
	// Not in use until 2.4.0
}

/**
 * Formbuilder Save Metabox
 * 
 * Creates the metabox used by FES to save
 * the formbuilder as well as submenu JavaScript.
 *
 * @since 2.0.0
 * @since 2.3.0 Also contains JS to correct current submenu selection.
 * @access public
 *
 * @return void
 */
function fes_formbuilder_save() {
	if ( get_the_ID() == EDD_FES()->helper->get_option( 'fes-submission-form', false ) ) {
		?>
		<script>
		(function($) {
			var menu = $( "#toplevel_page_fes-about" );

			menu.removeClass( 'wp-not-current-submenu' );
			menu.addClass( 'wp-has-current-submenu' );

			var link = $( "#toplevel_page_fes-about" ).find( ".toplevel_page_fes-about");

			link.removeClass( 'wp-not-current-submenu' );
			link.addClass( "wp-has-current-submenu" );
			link.addClass( "wp-menu-open" );

			var submenu = menu.find( "li:nth-child(4)" );
			submenu.addClass( "current" );
		})( jQuery );
		</script>
		<?php
	} else if ( get_the_ID() == EDD_FES()->helper->get_option( 'fes-profile-form', false ) ) {
		?>
		<script>
		(function($) {
			var menu = $( "#toplevel_page_fes-about" );

			menu.removeClass( 'wp-not-current-submenu' );
			menu.addClass( 'wp-has-current-submenu' );

			var link = $( "#toplevel_page_fes-about" ).find( ".toplevel_page_fes-about");

			link.removeClass( 'wp-not-current-submenu' );
			link.addClass( "wp-has-current-submenu" );
			link.addClass( "wp-menu-open" );

			var submenu = menu.find( "li:nth-child(6)" );
			submenu.addClass( "current" );
		})( jQuery );
		</script>
		<?php
	} else if ( get_the_ID() == EDD_FES()->helper->get_option( 'fes-registration-form', false ) ) {
		?>
		<script>
		(function($) {
			var menu = $( "#toplevel_page_fes-about" );

			menu.removeClass( 'wp-not-current-submenu' );
			menu.addClass( 'wp-has-current-submenu' );

			var link = $( "#toplevel_page_fes-about" ).find( ".toplevel_page_fes-about");

			link.removeClass( 'wp-not-current-submenu' );
			link.addClass( "wp-has-current-submenu" );
			link.addClass( "wp-menu-open" );

			var submenu = menu.find( "li:nth-child(5)" );
			submenu.addClass( "current" );
		})( jQuery );
		</script>
		<?php
	}
?>
	<div class="submitbox" id="submitpost">
		<div id="minor-publishing">
			<div id="minor-publishing-actions">
				<center>
					<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Save' ) ?>" />
					<input name="save" type="submit" class="button button-primary button-large" id="publish" accesskey="p" value="<?php esc_attr_e( 'Save' ) ?>" />
					<span class="spinner"></span>
				</center>
			</div>
			<div class="fes-clear"></div>
		</div>
	</div>
	<?php
}

/**
 * Formbuilder Specific Fields Metabox
 * 
 * Creates the metabox used by FES to allow
 * for the selection of specific fields. These fields
 * are designed to be only used on a subset of forms 
 * like post title for the submission form.
 *
 * @since 2.3.0
 * @access public
 *
 * @todo  Simplify this and the other 2 field
 *        metabox functions.
 * 
 * @return void
 */
function fes_formbuilder_sidebar_specfic() { ?>
	<div class="fes-form-buttons">
	<?php

	$id = get_the_ID();
	if ( empty( $id ) ){
		return;
	}

	$fkey  = '';
	$fname = '';
	foreach ( EDD_FES()->load_forms as $name => $class ) {
		$form = new $class( $name, 'name' );
		if ( $form->id == $id ) {
			$fname = $form->title();
			$fkey  = $form->name;
			break;
		}
	}

	$order = array();
	$order[ $fname . ' Form Specific Fields'] = array();
	/* foreach fields as field
	 * 		does field support this form? (index "forms" in supports )
	 * 			if yes output button. Name from defaults, label from defaults.
	 */
	foreach ( EDD_FES()->load_fields as $fid => $field ) {
		$class = EDD_FES()->load_fields[ $fid ];
		$fieldo = new $class;
		if ( isset( $fieldo->supports['position'] ) && isset( $fieldo->supports['forms'][ $fkey ] ) && $fieldo->supports['forms'][ $fkey ] ) {
			if ( $fieldo->supports['position'] == 'specific' ) {
				$order[ $fname . ' Form Specific Fields'][] = $fieldo;
			}
		}
	}
	$title = esc_attr( __( 'Click to add to the editor', 'edd_fes' ) );
	usort( $order[ $fname . ' Form Specific Fields'], 'fes_field_sort');
	foreach ( $order as $type => $index ) {
		if ( count( $index )  >= 1 ) {
			foreach ( $index as $fielde ) {
				echo '<button class="fes-button button" data-formid="' . get_the_ID() . '" data-name="'.$fielde->supports['template'].'" data-type="'.$fielde->supports['template'].'" title="' . $title . '">'. __( $fielde->supports['title'] , 'edd_fes' ) .'</button>';
			}
		}
		else {
			echo __( 'There are no form specific fields for this form', 'edd_fes' );
		}
	} ?>
  </div>
  <?php
}

/**
 * Formbuilder Custom Fields Metabox
 * 
 * Creates the metabox used by FES to allow
 * for the selection of custom fields. These fields
 * are generic in nature like url or textbox.
 *
 * @since 2.3.0
 * @access public
 *
 * @todo  Simplify this and the other 2 field
 *        metabox functions.
 * 
 * @return void
 */
function fes_formbuilder_sidebar_custom() { ?>
  <div class="fes-form-buttons">
  <?php
	$id = get_the_ID();
	if ( empty( $id ) ){
		return;
	}

	$fkey  = '';
	$fname = '';
	foreach ( EDD_FES()->load_forms as $name => $class ) {
		$form = new $class( $name, 'name' );
		if ( $form->id == $id ) {
			$fname = $form->title();
			$fkey  = $form->name;
			break;
		}
	}

	$order = array();
	$order['Custom Fields'] = array();
	/* foreach fields as field
	 * 		does field support this form? (index "forms" in supports )
	 * 			if yes output button. Name from defaults, label from defaults.
	 */
	foreach ( EDD_FES()->load_fields as $fid => $field ) {
		$class = EDD_FES()->load_fields[ $fid ];
		$fieldo = new $class;
		if ( isset( $fieldo->supports['position'] ) && isset( $fieldo->supports['forms'][ $fkey ] ) && $fieldo->supports['forms'][ $fkey ] ) {
			if ( $fieldo->supports['position'] == 'custom' ) {
				$order['Custom Fields'][] = $fieldo;
			}
		}
	}

	$title = esc_attr( __( 'Click to add to the editor', 'edd_fes' ) );
	usort( $order['Custom Fields'], 'fes_field_sort');
	foreach ( $order as $type => $index ) {
		if ( count( $index )  >= 1 ) {
			foreach ( $index as $fielde ) {
				echo '<button class="fes-button button" data-formid="' . get_the_ID() . '" data-name="'.$fielde->supports['template'].'" data-type="'.$fielde->supports['template'].'" title="' . $title . '">'. __( $fielde->supports['title'] , 'edd_fes' ) .'</button>';
			}
		}
		else {
			echo __( 'There are no custom fields for this form', 'edd_fes' );
		}
	} ?>
  </div>
  <?php
}

/**
 * Formbuilder Extension Fields Metabox.
 * 
 * Creates the metabox used by FES to allow
 * for the selection of extension fields. These fields
 * are added by extensions like commissions and also
 * from pre-2.3 fields.
 *
 * @since 2.3.0
 * @access public
 *
 * @todo  Simplify this and the other 2 field
 *        metabox functions.
 * 
 * @return void
 */
function fes_formbuilder_sidebar_extension() { ?>
  <div class="fes-form-buttons">
	  <?php
		$id = get_the_ID();
		if ( empty( $id ) ){
			return;
		}

		$fkey  = '';
		$fname = '';
		foreach ( EDD_FES()->load_forms as $name => $class ) {
			$form = new $class( $name, 'name' );
			if ( $form->id == $id ) {
				$fname = $form->title();
				$fkey  = $form->name;
				break;
			}
		}

		$order = array();
		$order['Extension Created Fields'] = array();
		/* foreach fields as field
		 * 		does field support this form? (index "forms" in supports )
		 * 			if yes output button. Name from defaults, label from defaults.
		 */		
		foreach ( EDD_FES()->load_fields as $fid => $field ) {
			$class = EDD_FES()->load_fields[ $fid ];
			$fieldo = new $class;
			if ( isset( $fieldo->supports['position'] ) && isset( $fieldo->supports['forms'][ $fkey ] ) && $fieldo->supports['forms'][ $fkey ] ) {
				if ( $fieldo->supports['position'] == 'extension' ) {
					$order['Extension Created Fields'][] = $fieldo;
				}
			}
		}

		$title = esc_attr( __( 'Click to add to the editor', 'edd_fes' ) );
		usort( $order['Extension Created Fields'], 'fes_field_sort');
		foreach ( $order as $type => $index ) {
			if ( count( $index )  >= 1 ) {
				foreach ( $index as $fielde ) {
					echo '<button class="fes-button button" data-formid="' . get_the_ID() . '" data-name="'.$fielde->supports['template'].'" data-type="'.$fielde->supports['template'].'" title="' . $title . '">'. __( $fielde->supports['title'] , 'edd_fes' ) .'</button>';
				}
			}
			else {
				if ( get_the_ID() == EDD_FES()->helper->get_option( 'fes-submission-form', false ) && !has_action( 'fes_custom_post_button' ) ) {
					echo __( 'There are no extension fields for this form', 'edd_fes' );
				} else if ( get_the_ID() == EDD_FES()->helper->get_option( 'fes-profile-form', false ) && !has_action( 'fes_custom_profile_button' ) ) {
					echo __( 'There are no extension fields for this form', 'edd_fes' );
				} else if ( get_the_ID() == EDD_FES()->helper->get_option( 'fes-registration-form', false ) && !has_action( 'fes_custom_registration_button' ) ) {
					echo __( 'There are no extension fields for this form', 'edd_fes' );
				}
			}
		}

		if ( get_the_ID() == EDD_FES()->helper->get_option( 'fes-submission-form', false ) ) {
			/**
			 * Output pre-FES 2.3 field buttons for Submission form.
			 *
			 * This outputs buttons for fields 
			 * that were made prior to the introduction
			 * of the FES_Fields API for the submission form.
			 *
			 * @since 2.0.0
			 *
			 * @deprecated 2.3.0
			 * @see  FES_Field
			 *
			 * @param string $title Text for the tooltip to add field.
			 */
			do_action( 'fes_custom_post_button', $title );
		} else if ( get_the_ID() == EDD_FES()->helper->get_option( 'fes-profile-form', false ) ) {
			/**
			 * Output pre-FES 2.3 field buttons for Profile form.
			 *
			 * This outputs buttons for fields 
			 * that were made prior to the introduction
			 * of the FES_Fields API for the profile form.
			 *
			 * @since 2.0.0
			 * @since 2.4.0 Added title param.
			 *
			 * @deprecated 2.3.0
			 * @see  FES_Field
			 *
			 * @param string $title Text for the tooltip to add field.
			 */
			do_action( 'fes_custom_profile_button', $title );
		} else if ( get_the_ID() == EDD_FES()->helper->get_option( 'fes-registration-form', false ) ) {
			/**
			 * Output pre-FES 2.3 field buttons for Registration form.
			 *
			 * This outputs buttons for fields 
			 * that were made prior to the introduction
			 * of the FES_Fields API for the registration form.
			 *
			 * @since 2.0.0
			 * @since 2.4.0 Added title param.
			 *
			 * @deprecated 2.3.0
			 * @see  FES_Field
			 *
			 * @param string $title Text for the tooltip to add field.
			 */
			do_action( 'fes_custom_registration_button', $title );
		} ?>
	</div>
	<?php
}

/**
 * Formbuilder Extension Fields Metabox.
 * 
 * Saves the FES formbuilder.
 *
 * @since 2.3.0
 * @access public
 *
 * @param  int $post_id Int ID of the current form.
 * @param  WP_Post $post Post object for the current form.
 * @return void
 */
function fes_forms_save_form( $post_id, $post ) {
	if ( empty( $post_id ) || $post_id < 1 || !is_object( $post ) || $post->post_type !== 'fes-forms' ) {
		return;
	}

	if ( isset( $_POST['fes-formbuilder-fields'] ) && wp_verify_nonce( $_POST['fes-formbuilder-fields'], 'fes-formbuilder-fields' ) ) {
		$values = $_POST['fes_input'];
		foreach ( EDD_FES()->load_forms as $name => $class ) {
			$form = new $class( $name, 'name' );
			if ( $form->id == $post->ID ) {
				$return = false;
				$return = $form->save_formbuilder_fields( $post->ID, $values );
				if ( $return ) {
					return $return;
				}
				break;
			}
		}
	}
}
add_action( 'save_post', 'fes_forms_save_form', 1, 2 );

/**
 * Add field to formbuilder.
 * 
 * Runs the ajax call that adds a field to
 * the current formbuilder.
 *
 * @since 2.0.0
 * @access public
 *
 * @return void
 */
function fes_forms_ajax_post_add_field() {
	if ( !isset( $_POST['action'] ) || $_POST['action'] !== 'fes_formbuilder' ) {
		exit;
	}
	$name     = isset( $_POST['name'] )  ? $_POST['name'] : '' ;
	$field_id = isset( $_POST['order'] ) ? $_POST['order'] : 0 ;
	$id       = isset( $_POST['id'] )    ? $_POST['id'] : 0 ;

	if ( fes_is_key( $name, EDD_FES()->load_fields ) ) {
		$class = EDD_FES()->load_fields[ $name ];
		$class = new $class( '', $id );
		echo $class->render_formbuilder_field( $field_id, true );
	} else {
		/**
		 * Output pre-FES 2.3 field in formbuilder.
		 *
		 * Adds a pre-FES 2.3 field to the formbuilder.
		 *
		 * @since 2.0.0
		 *
		 * @deprecated 2.3.0
		 * @see  FES_Field
		 *
		 * @param int $field_id Order of field in the formbuilder.
		 */		
		do_action( 'fes_admin_field_' . $name, $field_id );
	}
	exit;
}
add_action( 'wp_ajax_fes_formbuilder', 'fes_forms_ajax_post_add_field' );

/**
 * Sort FES Field buttons.
 * 
 * This function takes in 2 fields at a time
 * and using strcmp sorts the fields by their 
 * title.
 *
 * @since 2.3.0
 * @access public
 *
 * @param  FES_Field $a First FES field.
 * @param  FES_Field $b Second FES field.
 *
 * @return int The order of the fields (see PHP manual
 *                 for strcmp).
 */
function fes_field_sort( $a,$b ) {
	return strcmp($a->supports['title'],$b->supports['title']);
}