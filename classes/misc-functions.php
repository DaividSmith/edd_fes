<?php
/**
 * Misc Functions
 *
 * This file contains lots of little misc functions
 * used all over FES.
 *
 * @package FES
 * @subpackage Misc
 * @since 2.0.0
 *
 * @todo Split out classes into their own files.
 * @todo General function cleanup.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { 
	exit;
}

/**
 * Category Walker.
 *
 * Create HTML dropdown list of Categories.
 *
 * @since 2.1.0
 * @access public
 *
 * @deprecated 2.4.0
 */
class FES_Walker_Category_Multi extends Walker {

	/**
	 * Tree type
	 *
	 * @since 2.1.0
	 * @access public
	 * @var string $tree_type The taxonomy.
	 */	
	var $tree_type = 'category';

	/**
	 * Database fields to allow
	 *
	 * @since 2.1.0
	 * @access public
	 * @var array $db_fields Database fields.
	 */
	var $db_fields = array('parent' => 'parent', 'id' => 'term_id');

	/**
	 * Start Element.
	 *
	 * Outputs the HTML to start a new tree element.
	 *
	 * @since 2.1.0
	 * @access public
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $category Category data object.
	 * @param int $depth Depth of category. Used for padding.
	 * @param array $args Uses 'selected' and 'show_count' keys, if they exist.
	 * @param int $current_object_id Current object id of a tree branch.
	 * @return void
	 */	
	function start_el( &$output, $category, $depth = 0, $args = array(), $current_object_id = 0 ) {
		$pad = str_repeat( '&nbsp;', $depth * 3 );

		$cat_name = apply_filters( 'list_cats', $category->name, $category );
		$output .= "\t<option class=\"level-$depth\" value=\"" . $category->term_id . "\"";
		if ( in_array( $category->term_id, $args['selected_multiple'] ) )
			$output .= ' selected="selected"';
		$output .= '>';
		$output .= $pad . $cat_name;
		if ( $args['show_count'] )
			$output .= '&nbsp;&nbsp;(' . $category->count . ')';
		$output .= "</option>\n";
	}

}

/**
 * Category Checklist Walker.
 *
 * Create HTML checklist of Categories.
 *
 * @since 2.1.0
 * @access public
 *
 * @deprecated 2.4.0
 */
class FES_Walker_Category_Checklist extends Walker {
	/**
	 * Tree type
	 *
	 * @since 2.1.0
	 * @access public
	 * @var string $tree_type The taxonomy.
	 */	
	var $tree_type = 'category';
	/**
	 * Database fields to allow
	 *
	 * @since 2.1.0
	 * @access public
	 * @var array $db_fields Database fields.
	 * @todo  Decouple this
	 */
	var $db_fields = array('parent' => 'parent', 'id' => 'term_id');

	/**
	 * Start Level.
	 *
	 * Outputs the HTML to start a new tree branch level.
	 *
	 * @since 2.1.0
	 * @access public
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int $depth Depth of category. Used for padding.
	 * @param array $args Uses 'selected' and 'show_count' keys, if they exist.
	 * @return void
	 */
	function start_lvl( &$output, $depth = 0, $args = array()  ) {
		$indent = str_repeat( "\t", $depth );
		$output .= "$indent<ul class='children'>\n";
	}

	/**
	 * End Level.
	 *
	 * Outputs the HTML to end a new tree branch level.
	 *
	 * @since 2.1.0
	 * @access public
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int $depth Depth of category. Used for padding.
	 * @param array $args Uses 'selected' and 'show_count' keys, if they exist.
	 * @return void
	 */
	function end_lvl( &$output, $depth = 0, $args = array()  ) {
		$indent = str_repeat( "\t", $depth );
		$output .= "$indent</ul>\n";
	}

	/**
	 * Start Element.
	 *
	 * Outputs the HTML to start a new tree element.
	 *
	 * @since 2.1.0
	 * @access public
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $category Category data object.
	 * @param int $depth Depth of category. Used for padding.
	 * @param array $args Uses 'selected' and 'show_count' keys, if they exist.
	 * @param int $current_object_id Current object id of a tree branch.
	 * @return void
	 */
	function start_el( &$output, $category, $depth = 0, $args = array(), $current_object_id = 0 ) {
		extract( $args );
		if ( empty( $taxonomy ) ){
			$taxonomy = 'category';
		}

		if ( $taxonomy == 'category' ){
			$name = 'category';
		} else {
			$name = $taxonomy;
		}

		$output .= "\n<li id='{$taxonomy}-{$category->term_id}'>" . '<label class="selectit"><input value="' . $category->term_id . '" type="checkbox" name="' . $name . '[]" id="in-' . $taxonomy . '-' . $category->term_id . '"' . checked( in_array( $category->term_id, $selected_cats ), true, false ) . disabled( empty( $args['disabled'] ), false, false ) . ' /> ' . esc_html( apply_filters( 'the_category', $category->name ) ) . '</label>';
	}

	/**
	 * End Element.
	 *
	 * Outputs the HTML to end a new tree element.
	 *
	 * @since 2.1.0
	 * @access public
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $object Category data object.
	 * @param int $depth Depth of category. Used for padding.
	 * @param array $args Uses 'selected' and 'show_count' keys, if they exist.
	 * @return void
	 */
	function end_el(  &$output, $object, $depth = 0, $args = array()  ) {
		$output .= "</li>\n";
	}

}

/**
 * Category Checklist.
 *
 * Outputs the HTML to makes a checklist of a taxonomy.
 *
 * @since 2.1.0
 * @since 2.3.0 Allow for other taxonomies to use this.
 * @access public
 *
 * @param int $post_id ID of the download.
 * @param array $selected_cats Selected taxonomy items.
 * @param array $attr Attributes of the checklist.
 * @param string $taxonomy Taxonomy to retrieve.
 * @return void
 */
function fes_category_checklist( $post_id = 0, $selected_cats = false, $attr = array(), $taxonomy = 'download_category' ) {
	require_once ABSPATH . '/wp-admin/includes/template.php';

	$walker = new FES_Walker_Category_Checklist();

	$exclude_type = isset( $attr['exclude_type'] ) ? $attr['exclude_type'] : 'exclude';
	$exclude = $attr['exclude'];
	$attr['name'] = $taxonomy;

	$args = array(
		'taxonomy' => $taxonomy,
	);

	if ( $post_id ) {
		$args['selected_cats'] = wp_get_object_terms( $post_id, $taxonomy, array('fields' => 'ids') );
	} elseif ( $selected_cats ) {
		$args['selected_cats'] = $selected_cats;
	} else {
		$args['selected_cats'] = array();
	}

	$categories = (array) get_terms( $taxonomy, array(
		'hierarchical'     => 1,
		'hide_empty'       => 0,
		'orderby'          => $attr['orderby'],
		'order'            => $attr['order'],
		$exclude_type      => $exclude,
		'selected'         => $selected_cats,        
	) );

	echo '<ul class="fes-category-checklist">';
	echo call_user_func_array( array(&$walker, 'walk'), array($categories, 0, $args) );
	echo '</ul>';
}

/**
 * Associate attachment to a post.
 *
 * FES uses this to attach uploaded media items
 * to the download post type.
 *
 * @since 2.0
 * @access public
 *
 * @global $wpdb WordPress database object.
 *
 * @param int $attachment_id Upload media item's id.
 * @param int $post_id Download's post id.
 */
function fes_associate_attachment( $attachment_id, $post_id ) {
	global $wpdb;

	$wpdb->update(
		$wpdb->posts,
		array(
			'post_parent' => $post_id,
		),
		array(
			'ID' => $attachment_id,
		),
		array(
			'%d'
		),
		array(
			'%d'
		)
	);
}

/**
 * Get Vendor Avatar.
 *
 * User avatar wrapper for custom uploaded avatar.
 * 
 * @since 2.0
 * @access public
 *
 * @param string $avatar The avatar url retrieved.
 * @param string|int $id_or_email User id or email.
 * @param int $size Size of avatar to return (max is 512), with default of 96.
 * @param string $default Url for an image, defaults to the "Mystery Man".
 * @param string $alt Alternate text for the avatar.
 * @return string Image tag of the user avatar.
 */
function fes_get_avatar( $avatar, $id_or_email, $size, $default, $alt ) {

	if ( is_array( $alt ) && $alt['force_default'] ) {
		return $avatar;
	}
	if ( is_numeric( $id_or_email ) ) {
		$user = get_user_by( 'id', $id_or_email );
	} elseif ( is_object( $id_or_email ) ) {
		if ( $id_or_email->user_id != '0' ) {
			$user = get_user_by( 'id', $id_or_email->user_id );
		} else {
			return $avatar;
		}
	} else {
		$user = get_user_by( 'email', $id_or_email );
	}

	if ( ! $user ) {
		return $avatar;
	}

	// see if there is a user_avatar meta field
	$user_avatar = get_user_meta( $user->ID, 'user_avatar', true );
	if ( empty( $user_avatar ) ) {
		return $avatar;
	}

	return sprintf( '<img src="%1$s" alt="%2$s" height="%3$s" width="%3$s" class="avatar">', esc_url( $user_avatar ), $alt, $size );
}
add_filter( 'get_avatar', 'fes_get_avatar', 99, 5 );

/**
 * Update Avatar.
 *
 * Updates a vendor's avatar.
 * 
 * @since 2.0
 * @access public
 *
 * @param int $user_id The vendor's user id.
 * @param int $attachment_id The attachment id of the new avatar image.
 * @return void
 */
function fes_update_avatar( $user_id, $attachment_id ) {

	$upload_dir = wp_upload_dir();
	$relative_url = wp_get_attachment_url( $attachment_id );

	if ( function_exists( 'wp_get_image_editor' ) ) {
		// try to crop the photo if it's big
		$file_path = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $relative_url );

		// as the image upload process generated a bunch of images
		// try delete the intermediate sizes.
		$ext = strrchr( $file_path, '.' );
		$file_path_w_ext = str_replace( $ext, '', $file_path );
		$small_url = $file_path_w_ext . '-avatar' . $ext;

		$editor = wp_get_image_editor( $file_path );

		if ( !is_wp_error( $editor ) ) {
			$avatar_size = apply_filters('fes_avatar_size' , array( 100, 100 ), $user_id, $attachment_id);
			$editor->resize( $avatar_size[0], $avatar_size[1], true );
			$editor->save( $small_url );

			// if the file creation successfull, delete the original attachment
			if ( file_exists( $small_url ) ) {
				$relative_url = str_replace( $upload_dir['basedir'], $upload_dir['baseurl'], $small_url );

				// delete any previous avatar
				$prev_avatar = get_user_meta( $user_id, 'user_avatar', true );

				if ( !empty( $prev_avatar ) && $prev_avatar != $relative_url ) {
					$prev_avatar_path = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $prev_avatar );

					if ( file_exists( $prev_avatar_path ) ) {
						unlink( $prev_avatar_path );
					}
				}

				// now update new user avatar
				update_user_meta( $user_id, 'user_avatar', $relative_url );
			}
		}
	}
}

/**
 * Show custom fields in post content area.
 *
 * If the setting in the FES settings is turned on
 * this function automatically shows the values of 
 * public submission form fields in the content
 * area of the download post type.
 *
 * @since 2.3
 * @access public
 *
 * @global WP_Post $post Post object for current page.
 * 
 * @param string $content The content of the page.
 * @return string The content of the page.
 */
function fes_show_custom_fields( $content ) {
	global $post;

	if ( $post->post_type != 'download' ){
		return $content;
	}

	$show_custom = EDD_FES()->helper->get_option( 'fes-show-custom-meta', false );
	$form_id     = EDD_FES()->helper->get_option( 'fes-submission-form', false );

	if ( !$show_custom || !$form_id ) {
		return $content;
	}
	$form = EDD_FES()->helper->get_form_by_id( $form_id, $post->ID );
	$html = $form->display_fields();
	return $content . $html;
}
add_filter( 'the_content', 'fes_show_custom_fields' );



/**
 * Get attachment ID from a URL.
 *
 * FES stores the attachment ids for file fields. This
 * function gets the attachment ID from a URL.
 *
 * @since 2.1.8
 * @access public
 *
 * @link http://philipnewcomer.net/2012/11/get-the-attachment-id-from-an-image-url-in-wordpress/ Original Implementation
 * @todo  This could be improved alot.
 * @global type $wpdb
 * 
 * @param string $attachment_url URL of attachment.
 * @param int $author_id User ID of uploader.
 * @return int ID of the attachment.
 */
function fes_get_attachment_id_from_url( $attachment_url = '', $author_id = 0 ) {
	global $wpdb;

	$attachment_id = false;

	// If there is no url, return.
	if ( '' == $attachment_url )
		return;

	// Get the upload directory paths
	$upload_dir_paths = wp_upload_dir();

	// Make sure the upload path base directory exists in the attachment URL, to verify that we're working with a media library image
	if ( false !== strpos( $attachment_url, $upload_dir_paths['baseurl'] ) ) {

		// Don't remove this for now. See https://github.com/chriscct7/edd-fes/issues/662
		// todo: remove in 2.4 unless 662 is reopened
		// If this is the URL of an auto-generated thumbnail, get the URL of the original image
		// $attachment_url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $attachment_url );

		// Remove the upload path base directory from the attachment URL
		$attachment_url = str_replace( $upload_dir_paths['baseurl'] . '/', '', $attachment_url );

		// Remove the -avatar suffix
		$attachment_url = str_replace( '-avatar.', '.', $attachment_url );

		// If an author ID is specified
		$author = '';
		if ( ! empty( $author_id ) ) {
			$author = "wposts.post_author = $author_id AND";
		}

		// Finally, run a custom database query to get the attachment ID from the modified attachment URL
		$attachment_id = $wpdb->get_var( $wpdb->prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE $author wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'", $attachment_url ) );
	}

	return $attachment_id;
}

/**
 * Retrieve a list of the allowed HTML tags
 *
 * This array is fed into wp_kses to allow
 * specific HTML tags on textarea and other
 * fields that allow HTML.
 *
 * @since   2.2.2
 * @access  public
 *
 * @return array Allowed HTML tags.
*/
function fes_allowed_html_tags() {
	$tags = array(
		'p' => array(
			'class' => array(),
			'style' => array()
		),
		'h1' => array(
			'class' => array(),
			'style' => array()
		),
		'h2' => array(
			'class' => array(),
			'style' => array()
		),
		'h3' => array(
			'class' => array(),
			'style' => array()
		),
		'h4' => array(
			'class' => array(),
			'style' => array()
		),
		'h5' => array(
			'class' => array(),
			'style' => array()
		),
		'h6' => array(
			'class' => array(),
			'style' => array()
		),
		'span' => array(
			'class' => array(),
			'style' => array()
		),
		'a' => array(
			'href' => array(),
			'title' => array(),
			'class' => array(),
			'title' => array(),
			'style' => array()
		),
		'b' => array(),
		'strong' => array(),
		'em' => array(),
		'br' => array(),
		'img' => array(
			'src' => array(),
			'title' => array(),
			'alt' => array(),
			'class' => array(),
			'size' => array(),
			'width' => array(),
			'height' => array(),
			'style' => array()
		),
		'div' => array(
			'class' => array(),
			'style' => array()
		),
		'ul' => array(
			'class' => array(),
			'style' => array()
		),
		'ol' => array(
			'class' => array(),
			'style' => array()
		),
		'li' => array(
			'class' => array(),
			'style' => array()
		),
		'font' => array()
	);
	/**
	 * Allowed HTML Tags
	 *
	 * Filter the allowed HTML tags in FES fields.
	 *
	 * @since 2.2.2
	 * 
	 * @param array $tags Array of allowed HTML elements.
	 */
	return apply_filters( 'fes_allowed_html_tags', $tags );
}

/**
 * Retrieve the currently displayed vendor.
 *
 * This is used when display a vendor's store page.
 *
 * @since 2.2.10
 * @access public 
 *
 * @uses FES_Vendor_Shop::get_queried_vendor()
 * 
 * @return object|false WP User Object or false.
 */
function fes_get_vendor() {
	return EDD_FES()->vendor_shop->get_queried_vendor();
}

/**
 * Marks a function as deprecated and informs when it has been used.
 *
 * There is a hook fes_deprecated_function_run that will be called that can be used
 * to get the backtrace up to what file and function called the deprecated
 * function. Based on the one in EDD core.
 *
 * The current behavior is to trigger a user error if WP_DEBUG is true.
 *
 * This function is to be used in every function that is deprecated.
 *
 * @since 2.3.0
 * @access public
 *
 * @uses do_action() Calls 'fes_deprecated_function_run' and passes the function name, what to use instead,
 *   and the version the function was deprecated in.
 * @uses apply_filters() Calls 'fes_deprecated_function_trigger_error' and expects boolean value of true to do
 *   trigger or false to not trigger error.
 *   
 * @param string  $function    The function that was called
 * @param string  $version     The version of WordPress that deprecated the function
 * @param string  $replacement Optional. The function that should have been called
 * @param array   $backtrace   Optional. Contains stack backtrace of deprecated function
 * @return void
 */
function _fes_deprecated_function( $function, $version, $replacement = null, $backtrace = null ) {

	/**
	 * Deprecated Function Action.
	 *
	 * Allow plugin run an action on the use of a 
	 * deprecated function. This could be used to
	 * feed into an error logging program or file.
	 *
	 * @since 2.3.0
	 * 
	 * @param string  $function    The function that was called.
	 * @param string  $version     The version of WordPress that deprecated the function.
	 * @param string  $replacement Optional. The function that should have been called.
	 * @param array   $backtrace   Optional. Contains stack backtrace of deprecated function.
	 */	
	do_action( 'fes_deprecated_function_run', $function, $version, $replacement, $backtrace );

	$show_errors = EDD_FES()->vendors->user_is_admin();

	/**
	 * Output Error Trigger.
	 *
	 * Allow plugin to filter the output error trigger.
	 *
	 * @since 2.3.0
	 * 
	 * @param bool $show_errors Whether to show errors.
	 */
	$show_errors = apply_filters( 'fes_deprecated_function_trigger_error', $show_errors );
	if ( WP_DEBUG && $show_errors ) {
		if ( ! is_null( $replacement ) ) {
			trigger_error( sprintf( __( '%1$s is <strong>deprecated</strong> since Easy Digital Downloads Frontend Submissions version %2$s! Use %3$s instead.', 'edd_fes' ), $function, $version, $replacement ) );
			trigger_error(  print_r( $backtrace, 1 ) ); // Limited to previous 1028 characters, but since we only need to move back 1 in stack that should be fine.
			// Alternatively we could dump this to a file.
		} else {
			trigger_error( sprintf( __( '%1$s is <strong>deprecated</strong> since Easy Digital Downloads Frontend Submissions version %2$s.', 'edd_fes' ), $function, $version ) );
			trigger_error( print_r( $backtrace, 1 ) );// Limited to previous 1028 characters, but since we only need to move back 1 in stack that should be fine.
			// Alternatively we could dump this to a file.
		}
	}
}

/**
 * Marks something as deprecated.
 *
 * The current behavior is to trigger a user error if WP_DEBUG is true.
 *
 * @since 2.3.0
 * @access public
 *
 * @uses apply_filters() Calls 'fes_deprecated_trigger_error' and expects boolean value of true to do
 *   trigger or false to not trigger error.
 *
 * @param string  $message     Deprecation message shown.
 * @return void
 */
function _fes_deprecated( $message ) {

	/**
	 * Deprecated Message Filter.
	 *
	 * Allow plugin to filter the deprecated message.
	 *
	 * @since 2.3.0
	 * 
	 * @param string $message Error message.
	 */	
	do_action( 'fes_deprecated_run', $message );

	$show_errors = EDD_FES()->vendors->user_is_admin();

	/**
	 * Deprecated Error Trigger.
	 *
	 * Allow plugin to filter the output error trigger.
	 *
	 * @since 2.3.0
	 * 
	 * @param bool $show_errors Whether to show errors.
	 */
	$show_errors = apply_filters( 'fes_deprecated_trigger_error', $show_errors );
	if ( WP_DEBUG && $show_errors ) {
		trigger_error( sprintf( __( '%s', 'edd_fes' ), $message) );
	}
}

/**
 * Key Exists In Array.
 * 
 * This PHP function checks an associative array for a key of a particular name. 
 * This may seem trivial but FES does this alot.
 *
 * Example:
 * $a = array( "one" => 1, "two" => 2 );
 * if ( fes_is_key( "one", $a ) ) { … } // == true
 *
 * @since 2.3.0
 * @access public
 *
 * @param string  $needle    The key we're looking for.
 * @param array   $haystack  The array we're searching.
 *
 * @return bool True if key in array else false.
 */
function fes_is_key( $needle = '', $haystack = array() ) {
	if ( strlen( $needle ) > 0 && count( $haystack ) > 0 ) {
		if ( in_array( $needle, array_keys( $haystack ) ) ) {
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

/**
 * Key Value Pair Exists In Array.
 * 
 * This PHP function checks an associative array for a specific key with a particular value
 * This may seem trivial but FES does this alot.
 *
 * Example:
 * $a = array( "one" => 1, "two" => 2 );
 * if ( fes_is_key_value( "one", 1, $a ) ) { … } // == true
 *
 * @since 2.3.0
 * @access public
 *
 * @param string  $needle    The key we're looking in.
 * @param string  $needle    The value we're looking for.
 * @param array   $haystack  The array we're searching.
 *
 * @return bool True if key in array else false.
 */
function fes_has_key_value( $needle = '', $value = '', $haystack = array() ) {
	foreach ( $haystack as $item ){
		if ( isset( $item[ $needle ] ) && $item[ $needle ] == $value ){
			return true;
		}
	}
	return false;
}

/**
 * Convert Dashes to Underscore.
 * 
 * Converts all dashes in a string to underscores.
 *
 * @since 2.3.0
 * @access public
 *
 * @param string  $string    String to convert.
 * @return string Converted string.
 */
function fes_dash_to_lower( $string ){
	return str_replace( '-', '_', $string );
}

/**
 * Is Frontend.
 * 
 * Determines if user is on frontend. Defined
 * by not being in the admin, and not being in an
 * api request.
 *
 * @since 2.3.0
 * @access public
 *
 * @return bool Whether we are on frontend.
 */
function fes_is_frontend(){
	if ( !fes_is_api_request() && !fes_is_admin() ){
		return true;
	} else {
		return false;
	}
}

/**
 * Is Admin.
 * 
 * Determines if user is in admin.
 *
 * @since 2.3.0
 * @access public
 *
 * @return bool Whether we are in admin.
 */
function fes_is_admin(){
	$output = false;
	if ( is_admin() && !fes_is_api_request() && !fes_is_frontend_ajax_request() ){
		$output = true;
	}
	return $output;
}

/**
 * Is API Request.
 * 
 * For now unused. Reserved for future
 * use.
 *
 * @since 2.3.0
 * @access public
 *
 * @return bool Whether we are in api request.
 */
function fes_is_api_request(){
	return false;
}

/**
 * Is Ajax Request.
 * 
 * Determines if the user is in an
 * ajax request.
 *
 * @since 2.3.0
 * @access public
 *
 * @return bool Whether we are in ajax request.
 */
function fes_is_ajax_request(){
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ){
		return true;
	} else {
		return false;
	}
}

/**
 * Is Frontend Ajax Request.
 * 
 * Determines if the user is in an
 * frontend ajax request.
 *
 * @since 2.3.0
 * @access public
 *
 * @todo  There has to be a better way.
 * @todo  Make a custom ajax endpoint.
 * 
 * @return bool Whether we are in frontend ajax request.
 */
function fes_is_frontend_ajax_request(){
	$output = false;
	if ( fes_is_ajax_request() ){
		// This is a replication of (and replaces a call to) wp_get_referer() function, see https://core.trac.wordpress.org/ticket/25294
		// First we see if there's the server referrer and use that if possible, to see if its in the admin
		// If its not there we then try to use the referrer field
		// This is literally insanity but there is no better way for now. We'll use a custom AJAX endpoint to get rid of this nonsense in 2.4
		// unless WordPress and/or EDD can finish their proposed inprovements on this issue, and if so we'll use theirs.
		$ref = '';
		if ( ! empty( $_SERVER['HTTP_REFERER'] ) ){
			$ref = wp_unslash( $_SERVER['HTTP_REFERER'] );
			if ( strpos( $ref, admin_url() ) === false ){
				$output = true; // not found
			}
		} else if ( ! empty( $_REQUEST['_wp_http_referer'] ) ){
			$ref = wp_unslash( $_REQUEST['_wp_http_referer'] );
			if ( strpos( $ref, 'wp-admin' ) === false){
				$output = true; // not found
			}
		}
	}
	return $output;
}


// better FES upload files protection. Circa 2.3

/**
 * Change Downloads Upload Directory.
 *
 * Hooks the edd_set_upload_dir filter when appropriate. This function works by
 * hooking on the WordPress Media Uploader and moving the uploading files that
 * are used for EDD to an edd directory under wp-content/uploads/ therefore,
 * the new directory is wp-content/uploads/edd/{year}/{month}. This directory is
 * provides protection to anything uploaded to it.
 *
 * @since 2.3
 * @access public 
 *
 * @param array $file Unused but contains file being currently uploaded.
 * @return array File that was uploaded.
 */
function fes_change_downloads_upload_dir( $file ) {
	$override_default_dir = apply_filters('override_default_fes_dir', false );
	if ( EDD()->session->get( 'FES_FILE_UPLOAD' ) ) {
		if ( function_exists( 'edd_set_upload_dir' ) && !$override_default_dir ) {
			add_filter( 'upload_dir', 'edd_set_upload_dir' );
		} else if ( $override_default_dir ) {
			add_filter( 'upload_dir', 'fes_set_custom_upload_dir' );
		} else {
			// wierd. Should never get here
		}
	}
	return $file;
}
add_action( 'wp_handle_upload_prefilter', 'fes_change_downloads_upload_dir' );

/**
 * Turn on File Filter.
 * 
 * When this is active, intercepts all files and
 * puts them in the FES file directory.
 *
 * @since 2.3.0
 * @access public
 * 
 * @return void
 */
function fes_turn_on_file_filter(){
	if ( !EDD()->session->get( 'FES_FILE_UPLOAD' ) ) {
		EDD()->session->set( 'FES_FILE_UPLOAD', true );
	}
}
add_action( 'wp_ajax_fes_turn_on_file_filter', 'fes_turn_on_file_filter' );
add_action( 'wp_ajax_nopriv_fes_turn_on_file_filter', 'fes_turn_on_file_filter' );

/**
 * Turn off File Filter.
 * 
 * Used after an FES file finishes uploading.
 *
 * @since 2.3.0
 * @access public
 * 
 * @return void
 */
function fes_turn_off_file_filter(){
	if ( EDD()->session->get( 'FES_FILE_UPLOAD' )  ) {
		EDD()->session->set( 'FES_FILE_UPLOAD', false );
	}
}
add_action( 'wp_ajax_fes_turn_off_file_filter', 'fes_turn_off_file_filter' );
add_action( 'wp_ajax_nopriv_fes_turn_off_file_filter', 'fes_turn_off_file_filter' );

/**
 * FES System Info.
 * 
 * Adds stuff to the FES system info.
 *
 * @since 2.0.0
 * @access public
 *
 * @todo Only show FES's settings on the system
 *       info.
 * @todo Don't use global, use a helper.
 *
 * @global $fes_settings The FES settings.
 *
 * @param string $return System info text.
 * @return string System info text.
 */
function fes_after_edd_system_info ( $return ){
	global $fes_settings;
	ob_start(); 
	?>

### Begin FES Debugging Information ###

<?php do_action( 'fes_system_info_before' ); ?>
Dashboard URL:        <?php echo get_permalink( EDD_FES()->helper->get_option( 'fes-vendor-dashboard-page', false ) ) . "\n"; ?>
Settings: 
<?php
print_r( $fes_settings );
$posts = get_posts( array( 'post_type' => 'fes-forms', 'posts_per_page'=>- 1 ) );
foreach ( $posts as $post ) {
	echo $post->id ;
	echo get_the_title( $post->id );
	print_r( get_post_meta( $post->id, 'fes-form', false ) );
}
?>

<?php
// Show templates that have been copied to the theme's fes_templates dir
$dir = get_stylesheet_directory() . '/fes_templates/*';
	if ( is_dir( $dir ) && ( count( glob( "$dir/*" ) ) !== 0 ) ) {
		$return .= "\n" . '-- FES Template Overrides' . "\n\n";
		foreach( glob( $dir ) as $file ) {
			$return .= 'Filename:                 ' . basename( $file ) . "\n";
		}
}
?>
### End FES Debugging Information ###

<?php
	$return .= ob_get_contents();
	ob_end_clean();
	return $return;
}
add_filter( 'edd_sysinfo_after_session_config', 'fes_after_edd_system_info', 10, 1 );

/**
 * FES Easter Egg Mode.
 * 
 * Turns on Easter Egg functionality. Functionality this 
 * turns on is subject to change without warning. 
 * As easter eggs, these are not permenant features.
 * 
 * @since 2.3.0
 * @access public
 *
 * @return bool Is easter egg mode on.
 */
function fes_easter_egg_mode(){

	/**
	 * Easter Egg Mode Toggle.
	 *
	 * Turns on FES's easter eggs.
	 *
	 * @since 2.3.0
	 * 
	 * @param bool $on Whether to show easter eggs.
	 */
	$on = apply_filters( 'fes_easter_egg_mode', false );
	return $on;
}

/**
 * FES Beta Testing Mode.
 * 
 * Turns on beta test functionality. Functionality this 
 * turns on is subject to change without warning.
 * 
 * @since 2.3.0
 * @access public
 *
 * @return bool Is easter egg mode on.
 */
function fes_beta_test_mode(){

	/**
	 * Beta Testing Mode Toggle.
	 *
	 * Turns on FES's beta features.
	 *
	 * @since 2.3.0
	 * 
	 * @param bool $on Whether to show beta features.
	 */
	$on = apply_filters( 'fes_beta_test_mode', false );
	return $on;
}

/**
 * Ajax Taxonomy Search.
 * 
 * Runs the ajax search used for taxonomy
 * fields.
 * 
 * @since 2.3.0
 * @access public
 *
 * @global $wpdb WordPress database object.
 *
 * @return void
 */
function fes_ajax_taxonomy_search() {
	global $wpdb;
	if ( isset( $_GET['tax'] ) ) {
		$taxonomy = sanitize_key( $_GET['tax'] );
		$tax = get_taxonomy( $taxonomy );
		if ( ! $tax ){
			wp_die( 0 );
		}
	} else {
		wp_die( 0 );
	}

	if ( !EDD_FES()->vendors->user_is_vendor( get_current_user_id() ) ){
		wp_die( 0 );
	}

	$s = stripslashes( $_GET['q'] );
	$comma = _x( ',', 'tag delimiter' );
	if ( ',' !== $comma ){
		$s = str_replace( $comma, ',', $s );
	}
	if ( false !== strpos( $s, ',' ) ) {
		$s = explode( ',', $s );
		$s = $s[count( $s ) - 1];
	}
	$s = trim( $s );
	if ( strlen( $s ) < 2 ){
		wp_die(); // require 2 chars for matching
	}
	$results = $wpdb->get_col( $wpdb->prepare( "SELECT t.name FROM $wpdb->term_taxonomy AS tt INNER JOIN $wpdb->terms AS t ON tt.term_id = t.term_id WHERE tt.taxonomy = %s AND t.name LIKE (%s)", $taxonomy, '%' . $wpdb->esc_like( $s ) . '%' ) );
	echo join( $results, "\n" );
	wp_die();
}
add_action( 'wp_ajax_fes_ajax_taxonomy_search','fes_ajax_taxonomy_search' );
add_action( 'wp_ajax_nopriv_fes_ajax_taxonomy_search', 'fes_ajax_taxonomy_search' );

/**
 * Get Forms.
 * 
 * Gets all FES Forms in a ID to title array.
 * This is used to populate the select field
 * to assign FES Forms in the FES Form settings.
 * 
 * @since 2.3.0
 * @access public
 *
 * @param bool $force Force look for forms, even
 *                    if not on the right page.
 * @return array Array of FES Forms.
 */
function fes_get_forms( $force = false ) {

	$forms_options = array( '' => '' ); // Blank option

	if ( ( ! isset( $_GET['page'] ) || 'edd-settings' != $_GET['page'] ) && ! $force ) {
		return $forms_options;
	}

	$forms = get_posts( array( 'post_type' => 'fes-forms' ) );
	if ( $forms ) {
		foreach ( $forms as $form ) {
			$forms_options[ $form->ID ] = $form->post_title;
		}
	}

	return $forms_options;
}
