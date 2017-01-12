<?php
/**
 * FES Helpers
 *
 * This file contains helper functions that
 * are useful on the frontend and admin.
 *
 * @package FES
 * @subpackage Misc
 * @since 2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FES Helpers.
 *
 * Contains a bunch of useful functions, including
 * a lot of form retrieval and setting maniupulation
 * functions.
 *
 * @since 2.0.0
 * @access public
 */
class FES_Helpers {

	/**
	 * FES helper construct.
	 *
	 * Right now does nothing, but in the future
	 * will register all of the generic shortcodes
	 * coming in FES.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @return void
	 */
	function __construct() {
		//add_shortcode( 'fes_form_display_fields', array( $this,  'display_fields_shortcode' ) );
	}

	/**
	 * Get option.
	 *
	 * Gets FES setting for the given key.
	 * If not set, returns the default
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param string $key Key of setting to find.
	 * @param mixed $default Default to send back if setting not found.
	 * @return void
	 */
	public function get_option( $key = '', $default = false ) {
		$value = edd_get_option( $key, $default );
		return $value;
	}

	/**
	 * Set option.
	 *
	 * Sets FES setting for the given key with
	 * the passed in value.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param string $key Key of setting to find.
	 * @param mixed $value Value to set setting to.
	 * @return void
	 */
	public function set_option( $key, $value ) {
		edd_update_option( $key, $value );
		global $fes_settings, $edd_options;
		$fes_settings = $edd_options;
	}

	/**
	 * Get Form Name by ID.
	 *
	 * Retrieve an FES form name when you know
	 * the id of the form.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param int $id Post id of form.
	 * @return string Form name.
	 */
	public function get_form_name_by_id( $id ){
		$name = get_post_meta( $id, 'fes-form-name', true );
		if ( ! $name ){
			if ( EDD_FES()->helper->get_option( 'fes-submission-form', false ) == $id ) {
				$name = 'submission';
			} else if ( EDD_FES()->helper->get_option( 'fes-profile-form', false ) == $id  ) {
				$name = 'profile';
			} else if ( EDD_FES()->helper->get_option( 'fes-registration-form', false ) == $id  ) {
				$name = 'registration';
			} else if ( EDD_FES()->helper->get_option( 'fes-login-form', false ) == $id  ) {
				$name = 'login';
			} else if ( EDD_FES()->helper->get_option( 'fes-vendor-contact-form', false ) == $id  ) {
				$name = 'vendor-contact';
			}
		}
		return $name;
	}

	/**
	 * Get Form Type by ID.
	 *
	 * Retrieve an FES form type when you know
	 * the id of the form.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param int $id Post id of form.
	 * @return string Form type.
	 */
	public function get_form_type_by_id( $id ){
		$type = get_post_meta( $id, 'fes-form-type', true );
		if ( !$type ){
			if ( EDD_FES()->helper->get_option( 'fes-submission-form', false ) == $id ) {
				$type = 'post';
			} else if ( EDD_FES()->helper->get_option( 'fes-profile-form', false ) == $id  ) {
				$type = 'user';
			} else if ( EDD_FES()->helper->get_option( 'fes-registration-form', false ) == $id  ) {
				$type = 'user';
			} else if ( EDD_FES()->helper->get_option( 'fes-login-form', false ) == $id  ) {
				$type = 'custom';
			} else if ( EDD_FES()->helper->get_option( 'fes-vendor-contact-form', false ) == $id  ) {
				$type = 'custom';
			}
		}
		return $type;
	}

	/**
	 * Get Form Class by ID.
	 *
	 * Retrieve an FES form class when you know
	 * the id of the form.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param int $id Post id of form.
	 * @return string Form class.
	 */
	public function get_form_class_by_id( $id ){
		$class = get_post_meta( $id, 'fes-form-class', false );
		if ( !$class ){
			if ( EDD_FES()->helper->get_option( 'fes-submission-form', false ) == $id ) {
				$class = 'FES_Submission_Form';
			} else if ( EDD_FES()->helper->get_option( 'fes-profile-form', false ) == $id  ) {
				$class = 'FES_Profile_Form';
			} else if ( EDD_FES()->helper->get_option( 'fes-registration-form', false ) == $id  ) {
				$class = 'FES_Registration_Form';
			} else if ( EDD_FES()->helper->get_option( 'fes-login-form', false ) == $id  ) {
				$class = 'FES_Login_Form';
			} else if ( EDD_FES()->helper->get_option( 'fes-vendor-contact-form', false ) == $id  ) {
				$class = 'FES_Vendor_Contact_Form';
			}
		}
		return $class;
	}

	/**
	 * Get Form ID by Name.
	 *
	 * Retrieve an FES form ID when you know
	 * the name of the form.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param string $name Form name.
	 * @return int Form ID.
	 */
	public function get_form_id_by_name( $name ){
		return EDD_FES()->helper->get_option( 'fes-' . $name . '-form', false );
	}

	/**
	 * Get Form Class by Name.
	 *
	 * Retrieve an FES form Class when you know
	 * the name of the form.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param string $name Form name.
	 * @return string Form class.
	 */
	public function get_form_class_by_name( $name ){
		if ( fes_is_key( $name, EDD_FES()->load_forms ) ){
			return EDD_FES()->load_forms[ $name ];
		} else {
			return false;
		}
	}

	/**
	 * Get Form by Name.
	 *
	 * Retrieve an FES form object when you know
	 * the name of the form.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param string $name Form name.
	 * @param int $save_to ID of object to save to.
	 * @return FES_Form Form object.
	 */
	public function get_form_by_name( $name, $save_to = false ){
		$class = get_post_meta( EDD_FES()->helper->get_form_id_by_name( $name ), 'fes-form-class', true );
		if ( $class ){
			$form = new $class( EDD_FES()->helper->get_form_id_by_name( $name ), 'id', $save_to );
			return $form;
		} else {
			return false;
		}
	}

	/**
	 * Get Form by ID.
	 *
	 * Retrieve an FES form object when you know
	 * the ID of the form.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param int $id Form id.
	 * @param int $save_to ID of object to save to.
	 * @return FES_Form Form object.
	 */
	public function get_form_by_id( $id = 0, $save_to = false ) {

		$class = get_post_meta( $id, 'fes-form-class', true );

		if( ! empty( $class ) && class_exists( $class ) && false !== strpos( $class, 'FES_' ) ) {

			$form = new $class( $id, 'id', $save_to );

		} else {

			// Form class is invalid, try to reset it
			$this->reset_form_meta();

			$class = get_post_meta( $id, 'fes-form-class', true );

			if( is_string( $class ) && false !== strpos( $class, 'FES_' ) ) {

				$form = new $class( $id, 'id', $save_to );

			} else {

				$form = false;

			}

		}

		return $form;
	}

	/**
	 * Is FES Form.
	 *
	 * Based on the post id, see if the post
	 * is a FES Form.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param int $id Form id.
	 * @return bool Is FES Form.
	 */
	public function is_fes_form( $id = 0 ){
		$found = false;
		$form = get_post_meta( $id, 'fes-form-class', true );
		if ( !empty( $form ) ) {
			$found = true;
		}
		return $found;
	}

	/**
	 * Get FES Form Name by Class.
	 *
	 * Given the class of a form, find
	 * the form name.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param string $class Form class.
	 * @return string FES form name.
	 */
	public function get_form_name_by_class( $class ){
		if ( 'submission' == $class ) {
			$class = 'FES_Submission_Form';
		} else if ( 'profile' == $class  ) {
			$class = 'FES_Profile_Form';
		} else if ( 'registration' == $class  ) {
			$class = 'FES_Registration_Form';
		} else if ( 'login' == $class  ) {
			$class = 'FES_Login_Form';
		} else if ( 'vendor-contact' == $class  ) {
			$class = 'FES_Vendor_Contact_Form';
		}
		return $class;
	}

	/**
	 * Get FES Form Class by Name.
	 *
	 * Given the name of a form, find
	 * the form class.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param string $name Form name.
	 * @return string FES form class.
	 */
	public function get_field_class_by_name( $name ){
		if ( fes_is_key( $name, EDD_FES()->load_fields ) ){
			return EDD_FES()->load_fields[ $name ];
		} else {
			return false;
		}
	}

	/**
	 * Display Fields Shortcode.
	 *
	 * Displays the fields for a form.
	 *
	 * @since 2.4.0
	 * @access public
	 *
	 * @param array $atts Form attributes.
	 * @return string FES form values.
	 */
	public function display_fields_shortcode( $atts ){
		$a = shortcode_atts( array(
			'form_id' => -2,
			'save_id' => -2,
		), $atts );

		$form = EDD_FES()->helper->get_form_by_id( $a['form_id'], $a['save_id'] );
		if ( $form && is_object( $form ) ){
			return $form->display_fields();
		}
	}

	/**
	 * Get Product Constant Name.
	 *
	 * Gets the translated version of the constant,
	 * as set in the EDD settings panel.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @param bool $plural Plural if true, else singular.
	 * @param bool $uppercase Uppercase if true, else lowercase.
	 * @return string Constant requested.
	 */
	public function get_product_constant_name( $plural = false, $uppercase = true ) {
		$constant = EDD_FES()->helper->get_option( 'fes-product-constant', '' );
		// Products
		if ( $plural && $uppercase ) {
			$constant = ( isset( $constant ) && $constant != '' ) ? ucfirst( $constant ) . 's' : __( 'Products', 'edd_fes' );
			/**
			 * Product Plural Uppercase Constant.
			 *
			 * Gets the product plural uppercase constant to use all over
			 * FES.
			 *
			 * @since 2.3.0
			 *
			 * @param  string $constant Constant from the panel.
			 */
			$constant = apply_filters( 'fes_product_constant_plural_uppercase', $constant );
		}
		// products
		else if ( $plural ) {
			$constant = ( isset( $constant ) && $constant != '' ) ? $constant . 's' : __( 'products', 'edd_fes' );
			/**
			 * Product Plural Lowercase Constant.
			 *
			 * Gets the product plural lowercase constant to use all over
			 * FES.
			 *
			 * @since 2.3.0
			 *
			 * @param  string $constant Constant from the panel.
			 */
			$constant = apply_filters( 'fes_product_constant_plural_lowercase', $constant );
		}
		// Product
		else if ( !$plural && $uppercase ) {
			$constant = ( isset( $constant ) && $constant != '' ) ? ucfirst( $constant ) : __( 'Product', 'edd_fes' );
			/**
			 * Product Singular Uppercase Constant.
			 *
			 * Gets the product singular uppercase constant to use all over
			 * FES.
			 *
			 * @since 2.3.0
			 *
			 * @param  string $constant Constant from the panel.
			 */
			$constant = apply_filters( 'fes_product_constant_singular_uppercase', $constant );
		}
		// product
		else {
			$constant = ( isset( $constant ) && $constant != '' ) ? $constant : __( 'product', 'edd_fes' );
			/**
			 * Product Singular Lowercase Constant.
			 *
			 * Gets the product singular lowercase constant to use all over
			 * FES.
			 *
			 * @since 2.3.0
			 *
			 * @param  string $constant Constant from the panel.
			 */
			$constant = apply_filters( 'fes_product_constant_singular_lowercase', $constant );
		}
		return apply_filters ( 'fes_product_constant', $constant, $plural, $uppercase );
	}

	/**
	 * Get Vendor Constant Name.
	 *
	 * Gets the translated version of the constant,
	 * as set in the EDD settings panel.
	 *
	 * @since 2.3.0
	 * @access public
	 *
	 * @param bool $plural Plural if true, else singular.
	 * @param bool $uppercase Uppercase if true, else lowercase.
	 * @return string Constant requested.
	 */
	public function get_vendor_constant_name( $plural = false, $uppercase = true ) {
		$constant = EDD_FES()->helper->get_option( 'fes-vendor-constant', '' );
		// Vendors
		if ( $plural && $uppercase ) {
			$constant = ( isset( $constant ) && $constant != '' ) ? ucfirst( $constant ) . 's' : __( 'Vendors', 'edd_fes' );
			/**
			 * Vendor Plural Uppercase Constant.
			 *
			 * Gets the vendor singular uppercase constant to use all over
			 * FES.
			 *
			 * @since 2.3.0
			 *
			 * @param  string $constant Constant from the panel.
			 */
			$constant = apply_filters( 'fes_vendor_constant_plural_uppercase', $constant );
		}
		// vendors
		else if ( $plural ) {
			$constant = ( isset( $constant ) && $constant != '' ) ? $constant . 's' : __( 'vendors', 'edd_fes' );
			/**
			 * Vendor Plural Lowercase Constant.
			 *
			 * Gets the vendor singular lowercase constant to use all over
			 * FES.
			 *
			 * @since 2.3.0
			 *
			 * @param  string $constant Constant from the panel.
			 */
			$constant = apply_filters( 'fes_vendor_constant_plural_lowercase', $constant );
		}
		// Vendor
		else if ( !$plural && $uppercase ) {
			$constant = ( isset( $constant ) && $constant != '' ) ? ucfirst( $constant ) : __( 'Vendor', 'edd_fes' );
			/**
			 * Vendor Singular Uppercase Constant.
			 *
			 * Gets the vendor singular uppercase constant to use all over
			 * FES.
			 *
			 * @since 2.3.0
			 *
			 * @param  string $constant Constant from the panel.
			 */
			$constant = apply_filters( 'fes_vendor_constant_singular_uppercase', $constant );
		}
		// vendor
		else {
			$constant = ( isset( $constant ) && $constant != '' ) ? $constant : __( 'vendor', 'edd_fes' );
			/**
			 * Vendor Singular Lowercase Constant.
			 *
			 * Gets the vendor singular lowercase constant to use all over
			 * FES.
			 *
			 * @since 2.3.0
			 *
			 * @param  string $constant Constant from the panel.
			 */
			$constant = apply_filters( 'fes_vendor_constant_singular_lowercase', $constant );
		}
		return apply_filters ( 'fes_vendor_constant', $constant, $plural, $uppercase );
	}

	/**
	 * Get User Meta.
	 *
	 * Basically a get_user_meta helper function.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @deprecated 2.1.0 Seriously don't use this.
	 *
	 * @param string $name Key of meta to retrieve.
	 * @param int $user_id User id to get value from.
	 * @param string $type Type of form.
	 * @return mixed FES form value.
	 */
	public function get_user_meta( $name, $user_id, $type = 'normal'  ){
		_fes_deprecated_function( 'EDD_FES()->helpers->get_user_meta', '2.1', 'get_user_meta()' );
		if ( empty( $name ) || empty( $user_id ) ) {
			return;
		}

		if ( $type == 'image' || $type == 'file' ) {
			$images = get_user_meta( $user_id, $name );

			if ( $images ) {
				$html = '';
				if ( isset( $images[0] ) && is_array( $images[0] ) ){
					$images = $images[0];
				}
				foreach ($images as $attachment_id ) {
					if ( $type == 'image' ) {
						$thumb = wp_get_attachment_image( $attachment_id, $size );
					} else {
						$thumb = get_post_field( 'post_title', $attachment_id );
					}

					$full_size = wp_get_attachment_url( $attachment_id );
					$html .= sprintf( '<a href="%s">%s</a> ', $full_size, $thumb );
				}
				return $html;
			}
		} elseif ( $type == 'repeat' ) {
			return implode( '; ', get_user_meta( $user_id, $name ) );
		} else {
			return implode( ', ', get_user_meta( $user_id, $name ) );
		}

	}

	/**
	 * Get Post Meta.
	 *
	 * Basically a get_post_meta helper function.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @deprecated 2.1.0 Seriously don't use this.
	 *
	 * @param string $name Key of meta to retrieve.
	 * @param int $post_id Post id to get value from.
	 * @param string $type Type of form.
	 * @return mixed FES form value.
	 */
	public function get_post_meta( $name, $post_id, $type = 'normal' ){
		_fes_deprecated_function( 'EDD_FES()->helpers->get_post_meta', '2.1', 'get_post_meta()' );
		if ( empty( $name ) || empty( $post_id ) ) {
			return;
		}

		$post = get_post( $post_id );

		if ( $type == 'image' || $type == 'file' ) {
			$images = get_post_meta( $post->ID, $name );

			if ( $images ) {
				$html = '';
				if ( isset( $images[0] ) && is_array( $images[0] ) ){
					$images = $images[0];
				}
				foreach ($images as $attachment_id ) {
					if ( $type == 'image' ) {
						$thumb = wp_get_attachment_image( $attachment_id );
					} else {
						$thumb = get_post_field( 'post_title', $attachment_id );
					}

					$full_size = wp_get_attachment_url( $attachment_id );
					$html .= sprintf( '<a href="%s">%s</a> ', $full_size, $thumb );
				}
				return $html;
			}
		} elseif ( $type == 'repeat' ) {
			return implode( '; ', get_post_meta( $post->ID, $name ) );
		} else {
			return implode( ', ', get_post_meta( $post->ID, $name ) );
		}
	}

	/**
	 * Reset form metadata
	 *
	 * @since 2.4.4
	 * @access public
	 *
	 */
	public function reset_form_meta() {

		fes_save_initial_submission_form( EDD_FES()->helper->get_option( 'fes-submission-form', false ), false );
		fes_save_initial_profile_form( EDD_FES()->helper->get_option( 'fes-profile-form', false ), false );
		fes_save_initial_registration_form( EDD_FES()->helper->get_option( 'fes-registration-form', false ), false );
		fes_save_initial_login_form( EDD_FES()->helper->get_option( 'fes-login-form', false ), false );
		fes_save_initial_vendor_contact_form( EDD_FES()->helper->get_option( 'fes-vendor-contact-form', false ), false );

	}
}