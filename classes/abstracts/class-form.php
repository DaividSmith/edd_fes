<?php
class FES_Form {

	/** @var string The form ID. */
	public $id = null;

	/** @var string Version of form */
	public $version = '1.0.0';

	/** @var array Array of fields */
	public $fields = array();

	/** @var string The form's name (registration, contact etc). */
	public $name = null;

	/** @var string Title of the form */
	public $title = '';

	/** @var int The id of the object the form value is saved to. For a submission form, the $save_id is the post's ID, etc. */
	public $save_id = null;

	/** @var unknown Type of form: 'user', 'post', 'custom'. Dictates where the fields save their values. */
	public $type = 'custom';

	/** @var bool Whether or not entire form is readonly */
	public $readonly = false;

	/** @var array Array of things it supports */
	public $supports = array();

	/** @var array Array of characteristics of the form that need to be stored in the database */
	public $characteristics = array();

	/** @var array Array of notifications for the form. */
	public $notifications = array();

	/** Make form object */
	// key can either be the id (default) or name of form
	// by is id for id, name for get by name (possibly more like by class in the future )
	public function __construct( $key = 0, $by = 'id', $save_id = null ) {
		if ( $key === 0 ) { // let's fallback to login form if something catastrophic happens
			$key = EDD_FES()->helper->get_option( 'fes-login-form', 0 );
		}
		if ( $by === 'name' ) {
			$key = $this->get_form_id_by_name( $key );
			if ( !$key ) {
				return;
			}
		}

		$this->id              = $key;
		$this->save_id         = $save_id;

		$characteristics       = get_post_meta( $key, 'fes-characteristics', true );
		$characteristics       = !empty( $characteristics ) ? $characteristics : $this->characteristics;
		$this->characteristics = apply_filters( 'fes_form_construct_characteristics', $characteristics, $this );

		$notifications         = get_post_meta( $key, 'fes-notifications', true );
		$notifications         = !empty( $notifications ) ? $notifications : $this->notifications;
		$this->notifications   = apply_filters( 'fes_form_construct_notifications', $notifications, $this );

		$fields                = get_post_meta( $key, 'fes-form', true );
		$fields                = !empty( $fields ) ? $fields : array();
		$fields                = apply_filters( 'fes_form_construct_fields', $fields, $this );

		$this->load_fields( $fields );

		// use this to manipulate things like supports on instantiation
		do_action( 'fes_form_after_construct', $this );
		do_action( 'fes_' . $this->name() . '_form_after_construct', $this );

		$this->set_title();

		$this->extending_constructor();
	}

	public function extending_constructor() {
		// declared in extending form if wanted
	}

	public function get_fields( ) {
		$fields = $this->fields;
		$fields = apply_filters( 'fes_get_' . $this->name() . '_form_fields', $fields, $this );
		return $fields;
	}

	public function load_fields( $fields = array() ) {
		$final = array();
		if ( !empty( $fields ) ) {
			foreach ( $fields as $key => $value ) {
				if ( ! empty( $value['template'] ) && ! empty( $value['name'] ) ) {
					$class = EDD_FES()->helper->get_field_class_by_name( $value['template'] );
					if ( $class != '' && ! empty( $value['name'] ) ) {
						$final[ $value['name'] ] = new $class( $value, $this->id, $this->type, $this->save_id );
					} else {
						$final[ $value['name'] ] = $value;
					}
				}
			}
		}
		$final = apply_filters( 'fes_load_' . $this->name() . '_form_fields', $final, $this );
		$this->fields = $final;
	}

	/** Sets things this field supports. Hint: use $field->supports to get the things the field already supports.
	 If adding a support do something like $supports = $field->supports; $supports['something'] = true; $field->add_support( $support );
	 If removing do $supports = $field->supports; unset($supports['something']); $field->add_support( $support );
	 */
	public function add_support( $supports ) {
		$this->supports = $supports;
	}

	public function render_form( $user_id = -2, $readonly = -2 ) {
		$output = '';
		if ( fes_is_admin() ) {
			$output .= $this->render_form_admin( $user_id, $readonly );
		} else {
			$output .= $this->render_form_frontend( $user_id, $readonly );
		}
		return $output;
	}

	public function render_form_admin( $user_id = -2, $readonly = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $readonly === -2 ) {
			$readonly = $this->readonly;
		}

		$readonly = apply_filters( 'fes_render_' . $this->name() . '_form_admin_readonly', $readonly, $this, $user_id );
		$user_id  = apply_filters( 'fes_render_' . $this->name() . '_form_admin_user_id', $user_id, $this );
		$form     = apply_filters( 'fes_render_' . $this->name() . '_form_admin_form', true, $this );

		// See if can use form
		if ( !$this->can_render_form( false, true, $user_id ) ) {
			return $this->can_render_form( true, true, $user_id );
		}

		$output = '';

		$output = apply_filters( 'fes_render_' . $this->name() . '_form_admin_output_before_fields', $output, $this, $user_id, $readonly );
		do_action( 'fes_render_' . $this->name() . '_form_admin_before_fields', $this, $user_id, $readonly );
		do_action( 'fes_render_form_above_' . $this->name() . '_form', $this->save_id, $readonly );
		$fields = $this->fields;
		$fields = apply_filters( 'fes_render_' . $this->name() . '_form_admin_fields', $fields, $this, $user_id, $readonly );

		$count = 0;
		foreach ( $fields as $field ) {

			if ( ! is_object( $field ) ) {
				continue;
			}

			$templates_to_exclude = apply_filters( 'fes_templates_to_exclude_render_' . $this->name() . '_form_admin', array() );
			if ( is_array( $templates_to_exclude ) && in_array( $field->template(), $templates_to_exclude ) ) {
				continue;
			} else {
				$count++;
			}
		}

		if ( !empty( $fields ) && $count > 0 ) {
			if ( !$readonly && $form ) {
				$output .= '<form class="fes-ajax-form fes-' . $this->name() . '-form" action="" name="fes-' . $this->name() . '-form" method="post">';
			}
			$output .= '<div class="fes-form fes-' . $this->name() . '-form-div">';

			foreach ( $fields as $field ) {

				$templates_to_exclude = apply_filters( 'fes_templates_to_exclude_render_' . $this->name() . '_form_admin', array() );
				if ( is_object( $field ) && is_array( $templates_to_exclude ) && in_array( $field->supports['template'], $templates_to_exclude ) ) {
					continue;
				}

				$output .= apply_filters( 'fes_render_' . $this->name() . '_form_admin_fields_before_field', '', $field, $this, $user_id, $readonly );

				if ( is_object( $field ) && method_exists( $field, 'render_field' ) ) {
					$output .= $field->render_field_admin( $user_id, $readonly );
				}

				$output .= apply_filters( 'fes_render_' . $this->name() . '_form_admin_fields_after_field', '', $field, $this, $user_id, $readonly );
			}

			if ( !$readonly ) {
				$label     = apply_filters( 'fes_render_' . $this->name() . '_form_admin_submit_button_label', '', $this, $user_id );
				$show_args = apply_filters( 'fes_render_' . $this->name() . '_form_show_args_admin', true, $this, $user_id, $readonly );
				$args      = apply_filters( 'fes_render_' . $this->name() . '_form_args_admin', array(), $this, $user_id, $readonly );
				$output .= $this->submit_button( $label, $form, $show_args, $args );
			}

			$output .= '</div>';

			if ( !$readonly && $form ) {
				$output .= '</form>';
			}
		} else {
			$output .= __( 'The form has no custom fields!', 'edd_fes' );
		}

		do_action( 'fes_render_' . $this->name() . '_form_admin_after_fields', $this, $readonly );
		do_action( 'fes_render_form_below_' . $this->name() . '_form', $this->save_id, $readonly );
		$output = apply_filters( 'fes_render_' . $this->name() . '_form_admin_output_after_fields', $output, $this, $readonly );
		return $output;
	}

	public function render_form_frontend( $user_id = -2, $readonly = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $readonly === -2 ) {
			$readonly = $this->readonly;
		}

		$readonly = apply_filters( 'fes_render_' . $this->name() . '_form_frontend_readonly', $readonly, $this, $user_id );
		$user_id  = apply_filters( 'fes_render_' . $this->name() . '_form_frontend_user_id', $user_id, $this );
		$form     = apply_filters( 'fes_render_' . $this->name() . '_form_frontend_form', true, $this );

		// See if can use form
		if ( !$this->can_render_form( false, false, $user_id ) ) {
			return $this->can_render_form( true, false, $user_id );
		}

		$output = '';
		$output = apply_filters( 'fes_render_' . $this->name() . '_form_frontend_output_before_fields', $output, $this, $user_id, $readonly );
		do_action( 'fes_render_' . $this->name() . '_form_frontend_before_fields', $this, $user_id, $readonly );
		do_action( 'fes_render_form_above_' . $this->name() . '_form', $this->save_id, $readonly );
		$fields = $this->fields;
		$fields = apply_filters( 'fes_render_' . $this->name() . '_form_frontend_fields', $fields, $this, $user_id, $readonly );
		$count = 0;
		foreach ( $fields as $field ) {

			$templates_to_exclude = apply_filters( 'fes_templates_to_exclude_render_' . $this->name() . '_form_frontend', array() );
			if ( is_object( $field ) && is_array( $templates_to_exclude ) && in_array( $field->template(), $templates_to_exclude ) ) {
				continue;
			} else {
				$count++;
			}
		}

		if ( !empty( $fields ) && $count > 0 ) {
			if ( !$readonly && $form ) {
				$output .= '<form class="fes-ajax-form fes-' . $this->name() . '-form" action="" name="fes-' . $this->name() . '-form" method="post">';
			}
			$output .= '<div class="fes-form fes-' . $this->name() . '-form-div">';

			foreach ( $fields as $field ) {
				$templates_to_exclude = apply_filters( 'fes_templates_to_exclude_render_' . $this->name() . '_form_frontend', array() );
				if ( is_object( $field ) && is_array( $templates_to_exclude ) && in_array( $field->supports['template'], $templates_to_exclude ) ) {
					continue;
				}
				$output .= apply_filters( 'fes_render_' . $this->name() . '_form_frontend_fields_before_field', '', $field, $this, $user_id, $readonly );

				if ( is_object( $field ) && method_exists( $field, 'render_field' ) ) {
					$output .= $field->render_field_frontend( $user_id, $readonly );
				} else if ( isset( $field['template'] ) ) {
					_fes_deprecated( 'Outputting using a non FES Field is deprecated. Support will be removed in 2.4.' );
					ob_start();
					do_action( 'fes_render_field_' . $field['template'], $this->characteristics, $this->save_id, '' );
					$output .= ob_get_clean();
				}
				$output .= apply_filters( 'fes_render_' . $this->name() . '_form_frontend_fields_after_field', '', $field, $this, $user_id, $readonly );
			}

			if ( !$readonly ) {
				$label     = apply_filters( 'fes_render_' . $this->name() . '_form_frontend_submit_button_label', '', $this, $user_id );
				$show_args = apply_filters( 'fes_render_' . $this->name() . '_form_show_args_frontend', true, $this, $user_id, $readonly );
				$args      = apply_filters( 'fes_render_' . $this->name() . '_form_args_frontend', array(), $this, $user_id, $readonly );
				$output   .= $this->submit_button( $label, $form, $show_args, $args );
			}

			$output .= '</div>';

			if ( !$readonly && $form ) {
				$output .= '</form>';
			}
		} else {
			$output .= __( 'The form has no fields!', 'edd_fes' );
		}

		do_action( 'fes_render_' . $this->name() . '_form_frontend_after_fields', $this, $user_id, $readonly );
		do_action( 'fes_render_form_below_' . $this->name() . '_form', $this->save_id, $readonly );
		$output = apply_filters( 'fes_render_' . $this->name() . '_form_frontend_output_after_fields', $output, $this, $user_id, $readonly );
		return $output;
	}

	public function display_fields( $user_id = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		$user_id  = apply_filters( 'fes_' . $this->name() . '_form_display_fields_user_id', $user_id, $this );

		$output = '';
		$output = apply_filters( 'fes_' . $this->name() . '_form_display_fields_output_before_fields', $output, $this, $user_id );
		do_action( 'fes_' . $this->name() . '_form_display_fields_before_fields', $this, $user_id );

		$fields = $this->fields;
		$fields = apply_filters( 'fes_' . $this->name() . '_form_display_fields_fields', $fields, $this, $user_id );
		if ( !empty( $fields ) ) {
			$output = '<table class="fes-display-field-table fes-' . $this->name() . '-form-display-field-table">';
			foreach ( $fields as $field ) {
				if ( ! is_object( $field ) ) {
					continue;
				}

				if ( $field->is_public() ) {
					$output .= apply_filters( 'fes_' . $this->name() . '_form_display_fields_before_field', '', $field, $this, $user_id );
					$output .= $field->display_field( $user_id );
					$output .= apply_filters( 'fes_' . $this->name() . '_form_display_fields_after_field', '', $field, $this, $user_id );
				}
			}
			$output .= '</table>';
		} else {
			$output .= __( 'The form has no fields!', 'edd_fes' );
		}

		do_action( 'fes_' . $this->name() . '_form_display_fields_after_fields', $this, $user_id );
		$output = apply_filters( 'fes_' . $this->name() . '_form_display_fields_output_after_fields', $output, $this, $user_id );
		return $output;
	}

	public function save_form( $values = array(), $user_id = -2 ) {
		$output = array();
		if ( fes_is_admin() ) {
			$output = $this->save_form_admin( $values, $user_id );
		} else {
			$output = $this->save_form_frontend( $values, $user_id );
		}
		return $output;
	}

	public function save_form_admin( $values = array(), $user_id = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( !defined( 'DOING_FES_FORM_SUBMISSION' ) ) {
			define( 'DOING_FES_FORM_SUBMISSION', $this->id );
		}

		if ( !defined( 'DOING_FES_FORM_SUBMISSION_LOCATION' ) ) {
			define( 'DOING_FES_FORM_SUBMISSION_LOCATION', 'admin' );
		}

		$user_id  = apply_filters( 'fes_save_' . $this->name() . '_form_admin_user_id', $user_id, $this, $this->save_id );
		$values   = apply_filters( 'fes_save_' . $this->name() . '_form_admin_values', $values, $this, $this->save_id );
		if ( !( fes_is_admin() ) || ( !isset( $_REQUEST['fes-' . $this->name() .'-form'] ) || !wp_verify_nonce( $_REQUEST['fes-' . $this->name() .'-form'], 'fes-' . $this->name() .'-form' ) ) ) {
			return;
		}

		if ( fes_is_ajax_request() ) {
			check_ajax_referer( 'fes-' . $this->name() .'-form', 'fes-' . $this->name() .'-form' );
			@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		}

		$output = array(
			'success'     => false, // did the submission work?
			'errors'      => array(), // what errors were encountered on the field elements
			'redirect_to' => '#', // where should we go?
			'message'     => '', // what message should be provided?
		);

		// See if can save form
		if ( !$this->can_save_form( false, true, $user_id ) ) {
			$output['title'] = __( 'Error', 'edd_fes' );
			$output['message'] = $this->can_save_form( true, true, $user_id ); // what message should be provided?
			if ( fes_is_ajax_request() ) {
				echo json_encode( $output );
				exit;
			} else {
				return $output;
			}
		}

		do_action( 'fes_save_' . $this->name() . '_form_admin_values_before_save', $this, $user_id, $this->save_id );

		$fields = $this->fields;
		$fields = apply_filters( 'fes_save_' . $this->name() . '_form_admin_fields', $fields,  $this, $user_id, $this->save_id );

		if ( !empty( $fields ) ) {
			foreach ( $fields as $field ) {

				if ( ! is_object( $field ) ) {
					continue;
				}

				$templates_to_exclude = apply_filters( 'fes_templates_to_exclude_sanitize_' . $this->name() . '_form_admin', array() );
				if ( is_array( $templates_to_exclude ) && in_array( $field->supports['template'], $templates_to_exclude ) ) {
					continue;
				}
				$values = $field->sanitize( $values, $this->save_id, $user_id ); // this works like an apply_filters. Simply tack your error onto errors if needed
			}
			$output = $this->before_form_error_check_admin( $output, $this->save_id, $values, $user_id );
			if ( empty( $output['errors'] ) && empty( $output['message'] ) ) { // if all fields validated
				$output['success'] = true;
				$output['title'] = __( 'Success', 'edd_fes' );
				$output = $this->before_form_save( $output, $this->save_id, $values, $user_id );
				foreach ( $fields as $field ) {

					if ( ! is_object( $field ) ) {
						continue;
					}

					$templates_to_exclude = apply_filters( 'fes_templates_to_exclude_save_' . $this->name() . '_form_admin', array() );
					if ( is_array( $templates_to_exclude ) && in_array( $field->supports['template'], $templates_to_exclude ) ) {
						continue;
					}
					$field->save_field_values( $this->save_id, $values, $user_id );
				}
				$output = $this->after_form_save_admin( $output, $this->save_id, $values, $user_id );
				$this->trigger_notifications_admin( $output, $this->save_id, $values, $user_id );
				do_action( 'fes_save_' . $this->name() . '_form_admin_values_after_save', $output, $this, $user_id, $this->save_id );
				do_action( 'fes_save_' . $this->name() . '_form_after_admin', $values, $user_id );
				do_action( 'fes_save_' . $this->name() . '_form_values_after_save', $this, $user_id, $this->save_id );
			} else {
				if ( empty( $output['message'] ) ) {
					$output['message'] = __( 'Please fix the errors to proceed', 'edd_fes' ); // field validation failed
				}
				$output['title'] = __( 'Error', 'edd_fes' );
			}
		} else {
			$output['title'] = __( 'Error', 'edd_fes' );
			$output['message'] = __( 'There are no fields', 'edd_fes' ); // there are no fields on the form
		}
		if ( fes_is_ajax_request() ) {
			echo json_encode( $output );
			exit;
		} else {
			return $output;
		}
	}

	public function save_form_frontend( $values = array(), $user_id = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( !defined( 'DOING_FES_FORM_SUBMISSION' ) ) {
			define( 'DOING_FES_FORM_SUBMISSION', $this->id );
		}

		if ( !defined( 'DOING_FES_FORM_SUBMISSION_LOCATION' ) ) {
			define( 'DOING_FES_FORM_SUBMISSION_LOCATION', 'frontend' );
		}

		$user_id  = apply_filters( 'fes_save_' . $this->name() . '_form_frontend_user_id', $user_id, $this, $this->save_id );
		$values   = apply_filters( 'fes_save_' . $this->name() . '_form_frontend_values', $values, $this, $this->save_id );

		if ( ( fes_is_admin() ) || ( !isset( $_REQUEST['fes-' . $this->name() .'-form'] ) || !wp_verify_nonce( $_REQUEST['fes-' . $this->name() .'-form'], 'fes-' . $this->name() .'-form' ) ) ) {
			return;
		}

		if ( fes_is_ajax_request() ) {
			check_ajax_referer( 'fes-' . $this->name() .'-form', 'fes-' . $this->name() .'-form' );
			header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		}

		$output = array(
			'success'     => false, // did the submission work?
			'errors'      => array(), // what errors were encountered on the field elements
			'redirect_to' => '#', // where should we go?
			'message'     => '', // what message should be provided?
		);

		// See if can save form
		if ( !$this->can_save_form( false, false, $user_id ) ) {
			$output['title'] = __( 'Error', 'edd_fes' );
			$output['message'] = $this->can_save_form( true, false, $user_id ); // what message should be provided?
			if ( fes_is_ajax_request() ) {
				echo json_encode( $output );
				exit;
			} else {
				return $output;
			}
		}

		do_action( 'fes_save_' . $this->name() . '_form_frontend_values_before_save', $this, $user_id, $this->save_id );

		$fields = $this->fields;
		$fields = apply_filters( 'fes_save_' . $this->name() . '_form_frontend_fields', $fields,  $this, $user_id, $this->save_id );

		if ( ! empty( $fields ) ) {

			foreach ( $fields as $field ) {

				if ( ! is_object( $field ) ) {
					continue;
				}

				$templates_to_exclude = apply_filters( 'fes_templates_to_exclude_sanitize_' . $this->name() . '_form_frontend', array() );

				if ( is_array( $templates_to_exclude ) && in_array( $field->supports['template'], $templates_to_exclude ) ) {
					continue;
				}

				$values = $field->sanitize( $values, $this->save_id, $user_id ); // this works like an apply_filters. Locate your value and sanitize it

			}

			foreach ( $fields as $field ) {

				if ( ! is_object( $field ) ) {
					continue;
				}

				$templates_to_exclude = apply_filters( 'fes_templates_to_exclude_validate_' . $this->name() . '_form_frontend', array() );

				if ( is_array( $templates_to_exclude ) && in_array( $field->supports['template'], $templates_to_exclude ) ) {
					continue;
				}

				$error = false;
				$error = $field->validate( $values, $this->save_id, $user_id );

				if ( $error ) {
					$output['errors'][$field->name()] = $error;
				}

			}

			$output = $this->before_form_error_check_frontend( $output, $this->save_id, $values, $user_id );
			if ( empty( $output['errors'] ) && empty( $output['message'] ) ) { // if all fields validated
				$output['success'] = true;
				$output['title'] = __( 'Success', 'edd_fes' );
				$output = $this->before_form_save( $output, $this->save_id, $values, $user_id );
				foreach ( $fields as $field ) {

					if ( ! is_object( $field ) ) {
						continue;
					}

					$templates_to_exclude = apply_filters( 'fes_templates_to_exclude_save_' . $this->name() . '_form_frontend', array() );

					if ( is_array( $templates_to_exclude ) && in_array( $field->supports['template'], $templates_to_exclude ) ) {
						continue;
					}

					$field->save_field_values( $this->save_id, $values, $user_id );

				}

				$output = $this->after_form_save_frontend( $output, $this->save_id, $values, $user_id );
				$this->trigger_notifications_frontend( $output, $this->save_id, $values, $user_id );
				do_action( 'fes_save_' . $this->name() . '_form_frontend_values_after_save', $output, $this, $user_id, $this->save_id );
				do_action( 'fes_save_' . $this->name() . '_form_after_frontend', $values, $user_id );
				do_action( 'fes_save_' . $this->name() . '_form_values_after_save', $this, $user_id, $this->save_id );
			} else {
				if ( empty( $output['message'] ) ) {
					$output['message'] = __( 'Please fix the errors to proceed', 'edd_fes' ); // field validation failed
				}
				$output['title'] = __( 'Error', 'edd_fes' );
			}
		} else {
			$output['title'] = __( 'Error', 'edd_fes' );
			$output['message'] = __( 'There are no fields', 'edd_fes' ); // there are no fields on the form
		}
		if ( fes_is_ajax_request() ) {
			echo json_encode( $output );
			exit;
		} else {
			return $output;
		}
	}

	public function before_form_error_check( $output = array(), $save_id = -2, $values = array(), $user_id = -2 ) {
		if ( fes_is_admin() ) {
			$output = $this->before_form_error_check_admin( $output, $save_id, $values, $user_id );
		} else {
			$output = $this->before_form_error_check_frontend( $output, $save_id, $values, $user_id );
		}
		return $output;
	}

	public function before_form_error_check_frontend( $output = array(), $save_id = -2, $values = array(), $user_id = -2 ) {
		do_action( 'fes_before_' . $this->name() . '_form_error_check_action_frontend', $output, $save_id, $values, $user_id );
		$output = apply_filters( 'fes_before_' . $this->name() . '_form_error_check_frontend', $output, $save_id, $values, $user_id );
		return $output;
	}

	public function before_form_error_check_admin( $output = array(), $save_id = -2, $values = array(), $user_id = -2 ) {
		do_action( 'fes_before_' . $this->name() . '_form_error_check_action_admin', $output, $save_id, $values, $user_id );
		$output = apply_filters( 'fes_before_' . $this->name() . '_form_error_check_admin', $output, $save_id, $values, $user_id );
		return $output;
	}

	public function before_form_save( $output = array(), $save_id = -2, $values = array(), $user_id = -2 ) {
		if ( fes_is_admin() ) {
			$output = $this->before_form_save_admin( $output, $save_id, $values, $user_id );
		} else {
			$output = $this->before_form_save_frontend( $output, $save_id, $values, $user_id );
		}
		return $output;
	}

	public function before_form_save_frontend( $output = array(), $save_id = -2, $values = array(), $user_id = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}
		do_action( 'fes_before_' . $this->name() . '_form_save_frontend', $output, $save_id, $values, $user_id );
		$output = apply_filters( 'fes_before_' . $this->name() . '_form_save_frontend', $output, $save_id, $values, $user_id );
		return $output;
	}

	public function before_form_save_admin( $output = array(), $save_id = -2, $values = array(), $user_id = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}
		do_action( 'fes_before_' . $this->name() . '_form_save_admin', $output, $save_id, $values, $user_id );
		$output = apply_filters( 'fes_before_' . $this->name() . '_form_save_admin', $output, $save_id, $values, $user_id );
		return $output;
	}

	public function after_form_save( $output = array(), $save_id = -2, $values = array(), $user_id = -2 ) {
		if ( fes_is_admin() ) {
			$output = $this->after_form_save_admin( $output, $save_id, $values, $user_id );
		} else {
			$output = $this->after_form_save_frontend( $output, $save_id, $values, $user_id );
		}
		return $output;
	}

	public function after_form_save_frontend( $output = array(), $save_id = -2, $values = array(), $user_id = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}
		do_action( 'fes_after_' . $this->name() . '_form_save_frontend', $output, $save_id, $values, $user_id );
		$output = apply_filters( 'fes_after_' . $this->name() . '_form_save_frontend', $output, $save_id, $values, $user_id );
		return $output;
	}

	public function after_form_save_admin( $output = array(), $save_id = -2, $values = array(), $user_id = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}
		do_action( 'fes_after_' . $this->name() . '_form_save_admin', $output, $save_id, $values, $user_id );
		$output = apply_filters( 'fes_after_' . $this->name() . '_form_save_admin', $output, $save_id, $values, $user_id );
		return $output;
	}

	public function render_formbuilder_fields() {
		$output  = apply_filters( 'fes_render_' . $this->name() . '_form_formbuilder_output_before_loop', '' );
		$fields = $this->fields;
		$fields = apply_filters( 'fes_render_' . $this->name() . '_form_formbuilder_fields', $fields );

		if ( !empty( $fields ) ) {
			if ( EDD_FES()->vendors->can_save_formbuilder( ) ) {
				foreach ( $fields as $index => $field ) {
					$output .= apply_filters( 'fes_render_' . $this->name() . '_form_formbuilder_before_field', '', $index, $field );
					$output .= $field->render_formbuilder_field( $index, $field );
					$output .= apply_filters( 'fes_render_' . $this->name() . '_form_formbuilder_after_field', '', $index, $field );
				}
			}
		}

		$output  = apply_filters( 'fes_render_' . $this->name() . '_form_formbuilder_output_after_loop', $output );
		return $output;
	}

	public function render_formbuilder_settings() {
		// render formbuilder settings in 2.4
	}

	public function render_formbuilder_notifications() {
		// render formbuilder notifications in 2.4
	}

	public function trigger_notifications( $output = array(), $save_id = -2, $values = array(), $user_id = -2 ) {
		if ( fes_is_admin() ) {
			$this->trigger_notifications_admin( $output, $save_id, $values, $user_id );
		} else {
			$this->trigger_notifications_frontend( $output, $save_id, $values, $user_id );
		}
	}

	public function trigger_notifications_frontend( $output = array(), $save_id = -2, $values = array(), $user_id = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}
		do_action( 'fes_trigger_' . $this->name() . '_form_notifications_frontend', $output, $save_id, $values, $user_id );
		if ( !empty( $this->notifications ) ) {
			foreach ( $this->notifications as $notification ) {
				// Prepare for notification api in 2.4
			}
		} else {

		}
	}

	public function trigger_notifications_admin( $output = array(), $save_id = -2, $values = array(), $user_id = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}
		do_action( 'fes_trigger_' . $this->name() . '_form_notifications_admin', $output, $save_id, $values, $user_id );
		if ( !empty( $this->notifications ) ) {
			foreach ( $this->notifications as $notification ) {
				// Prepare for notification api in 2.4
			}
		} else {

		}
	}

	/** Saves the formbuilder fields. post_id is the post->ID of the form **/
	public function save_formbuilder_fields( $post_id = -2, $values = array() ) {

		if ( $post_id === -2 ) {
			$post_id = get_the_ID();
		}

		if ( !EDD_FES()->vendors->can_save_formbuilder( $post_id ) ) {
			return $post_id;
		}

		if ( !empty( $values ) ) {
			foreach ( $values as $id => $value ) {
				if ( isset ( $value['label'] ) ) {
					$value['label'] = sanitize_key( $value['label'] );
				}
				if ( isset ( $value['name'] ) ) {
					$value['name'] = sanitize_key( $value['name'] );
				}
			}
		}

		$values  = apply_filters( 'fes_save_' . $this->name() . '_form_formbuilder_fields_values', $values );

		do_action( 'fes_save_' . $this->name() . '_form_formbuilder_fields_before_save', $values );

		update_post_meta( $post_id, 'fes-form', $values  );

		do_action( 'fes_save_' . $this->name() . '_form_formbuilder_fields_after_save', $values );
	}

	/** Saves the formbuilder settings. post_id is the post->ID of the form **/
	public function save_formbuilder_settings( $post_id = -2, $values = array() ) {
		if ( $post_id === -2 ) {
			$post_id = get_the_ID();
		}

		if ( !EDD_FES()->vendors->can_save_formbuilder( $post_id ) ) {
			return $post_id;
		}

		$values  = apply_filters( 'fes_save_' . $this->name() . '_form_formbuilder_settings_values', $values );

		do_action( 'fes_save_' . $this->name() . '_form_formbuilder_settings_before_save', $values );

		update_post_meta( $post_id, 'fes-settings', $values  );

		do_action( 'fes_save_' . $this->name() . '_form_formbuilder_settings_after_save', $values );
	}

	/** Saves the formbuilder settings. post_id is the post->ID of the form **/
	public function save_formbuilder_notifications( $post_id = -2, $values = array() ) {
		if ( $post_id === -2 ) {
			$post_id = get_the_ID();
		}

		if ( !EDD_FES()->vendors->can_save_formbuilder( $post_id ) ) {
			return $post_id;
		}

		$values  = apply_filters( 'fes_save_' . $this->name() . '_form_formbuilder_notifications_values', $values );

		do_action( 'fes_save_' . $this->name() . '_form_formbuilder_notifications_before_save', $values );

		update_post_meta( $post_id, 'fes-notifications', $values  );

		do_action( 'fes_save_' . $this->name() . '_form_formbuilder_notificationss_after_save', $values );
	}

	public function get_form_values( $user_id = -2, $public = -2 ) {
		$output = array();
		if ( fes_is_admin() ) {
			$output = array_merge( $output, $this->get_form_values_admin( $user_id, $public ) );
		} else {
			$output = array_merge( $output, $this->get_form_values_frontend( $user_id, $public ) );
		}
		$output = apply_filters( 'fes_get_' . $this->name() . '_form_values_after_fork', $output, $this, $user_id, $public );
		return $output;
	}

	public function get_form_values_admin( $user_id = -2, $public = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $public === -2 ) {
			$public = $this->public;
		}

		$user_id  = apply_filters( 'fes_get_' . $this->name() . '_form_values_admin_user_id', $user_id, $this, $public );
		$public = apply_filters( 'fes_get_' . $this->name() . '_form_values_admin_public', $public, $this, $user_id, $public );

		$output = array();
		$output = apply_filters( 'fes_get_' . $this->name() . '_form_values_admin_before_fork', $output, $this, $user_id, $public );

		$fields = $this->fields;
		$fields = apply_filters( 'fes_save_' . $this->name() . '_form_admin_fields', $fields, $this, $user_id, $this->save_id, $public );
		if ( !empty( $fields ) ) {
			if ( EDD_FES()->vendors->can_get_form_values( $this->save_id, true, $user_id, $public ) ) {
				foreach ( $fields as $field ) {
					if ( ! is_object( $field ) ) {
						continue;
					}
					$output[ $field->id ] = $field->get_field_value_admin( $this->save_id, $user_id, $public );
				}
			}
		}

		$output = apply_filters( 'fes_get_' . $this->name() . '_form_values_admin_after_fork', $output, $this, $user_id, $public );
		return $output;
	}

	public function get_form_values_frontend( $user_id = -2, $public = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $public === -2 ) {
			$public = $this->public;
		}

		$user_id  = apply_filters( 'fes_get_' . $this->name() . '_form_values_frontend_user_id', $user_id, $this, $public );
		$public = apply_filters( 'fes_get_' . $this->name() . '_form_values_frontend_public', $public, $this, $user_id, $public );

		$output = array();
		$output = apply_filters( 'fes_get_' . $this->name() . '_form_values_frontend_before_fork', $output, $this, $user_id, $public );

		$fields = $this->fields;
		$fields = apply_filters( 'fes_save_' . $this->name() . '_form_frontend_fields', $fields, $this, $user_id, $this->save_id, $public );

		if ( !empty( $fields ) ) {
			if ( EDD_FES()->vendors->can_get_form_values( $this->save_id, false, $user_id, $public ) ) {
				foreach ( $fields as $field ) {
					if ( ! is_object( $field ) ) {
						continue;
					}
					$output[ $field->id ] = $field->get_field_value_frontend( $this->save_id, $user_id, $public );
				}
			}
		}

		$output = apply_filters( 'fes_get_' . $this->name() . '_form_values_frontend_after_fork', $output, $this, $user_id, $public );
		return $output;
	}

	/** Used when you need to change the save_id of the form and all of it's fields */
	public function change_save_id( $save_id ) {
		$this->save_id = $save_id;
		$fields                = get_post_meta( $this->id, 'fes-form', true );
		$fields                = !empty( $fields ) ? $fields : array();
		$this->load_fields( $fields );
	}

	/** Used when you need to change the readonly status of the form and all of it's fields */
	public function change_readonly( $readonly ) {
		$this->readonly = $readonly;
		$fields                = get_post_meta( $this->id, 'fes-form', true );
		$fields                = !empty( $fields ) ? $fields : array();
		$this->load_fields( $fields );
	}

	/** Used when you need to change the public status of the form and all of it's fields */
	public function change_public( $public ) {
		$this->public = $public;
		$fields                = get_post_meta( $this->id, 'fes-form', true );
		$fields                = !empty( $fields ) ? $fields : array();
		$this->load_fields( $fields );
	}

	public function has_formbuilder() {
		if ( isset( $this->supports['formbuilder'] ) ) {
			return true;
		} else {
			return false;
		}
	}

	public function is_form_set( $id = false ) {
		if ( $id == false ) {
			return false;
		} else {
			if ( EDD_FES()->helper->get_option( 'fes-'. $id . '-form', false ) ) {
				return true;
			} else {
				return false;
			}
		}
	}

	public function is_formbuilder( $id ) {
		if ( $id == $this->id ) {
			return true;
		} else {
			return false;
		}
	}

	public function name() {
		return $this->name;
	}

	public function get_form_id_by_name( $name ) {
		return EDD_FES()->helper->get_option( 'fes-'. $name . '-form', false );
	}

	public function set_title() {
		$title = _x( 'Submission', 'FES Form title translation', 'edd_fes' );
		$title = apply_filters( 'fes_' . $this->name() . '_form_title', $this->title );
		$this->title = $title;
	}

	public function title( $form = false ) {
		if ( $form ) {
			return sprintf( _x( "%s Form", '%s = FES Form Name (translated)', 'edd_fes' ), $this->title );
		} else {
			return $this->title;
		}
	}

	public function submit_button( $label = '', $form = true, $show_args = true, $args = array() ) {
		$color = edd_get_option( 'checkout_color', 'blue' );
		$color = ( $color == 'inherit' ) ? '' : $color;
		$style = edd_get_option( 'button_style', 'button' );

		if ( !$label ) {
			$label = __( "Submit", "edd_fes" );
		}

		$default = $this->get_submit_button_defaults( $form, $args );

		$args = array_merge( $default, $args );

		$output = '<fieldset class="fes-submit">';

		if ( $show_args ) {
			if ( ! empty( $args ) ) {
				foreach ( $args as $name => $value ) {
					$output .= '<input type="hidden" name="' . $name . '" value="' . $value . '">';
				}
			}
		}

		$referer = $form ? true : false;
		$output .= wp_nonce_field( 'fes-' . $this->name() .'-form', 'fes-' . $this->name() .'-form', $referer, false );
		if ( $form ) {
			$output .= '<input type="submit" class="edd-submit ' . $color . ' ' . $style . '" name="submit" value="' . $label . '" />';
		}
		$output .= '</fieldset>';

		return $output;
	}

	public function can_render_form( $output = false, $is_admin = -2, $user_id = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}
		if ( $is_admin === -2 ) {
			if ( fes_is_admin() ) {
				$is_admin = true;
			} else {
				$is_admin = false;
			}
		}

		$is_a_vendor = EDD_FES()->vendors->user_is_vendor( $user_id );
		$is_a_admin  = EDD_FES()->vendors->user_is_admin( $user_id );

		if ( $is_admin ) {
			if ( !$is_a_admin && !$is_a_vendor ) {
				if ( $output ) {
					return sprintf( __( 'Access Denied: You are not an admin or a %s', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = false ) );
				} else {
					return false;
				}
			}
		} else {
			if ( !$is_a_admin && !$is_a_vendor ) {
				if ( $output ) {
					return sprintf( __( 'Access Denied: You are not an admin or a %s', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = false ) );
				} else {
					return false;
				}
			}
		}
		return true;
	}

	public function can_save_form( $output = false, $is_admin = -2, $user_id = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}
		if ( $is_admin === -2 ) {
			if ( fes_is_admin() ) {
				$is_admin = true;
			} else {
				$is_admin = false;
			}
		}

		$is_a_vendor = EDD_FES()->vendors->user_is_vendor( $user_id );
		$is_a_admin  = EDD_FES()->vendors->user_is_admin( $user_id );

		if ( $is_admin ) {
			if ( !$is_a_admin && !$is_a_vendor ) {
				if ( $output ) {
					return sprintf( __( 'Access Denied: You are not an admin or a %s', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = false ) );
				} else {
					return false;
				}
			}
		} else {
			if ( !$is_a_admin && !$is_a_vendor ) {
				if ( $output ) {
					return sprintf( __( 'Access Denied: You are not an admin or a %s', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = false, $uppercase = false ) );
				} else {
					return false;
				}
			}
		}
		return true;
	}

	public function get_submit_button_defaults( $form, $args ) {
		// extend in classes that need it
		$default = array(
			'user_id'    => get_current_user_id(),
			'page_id'  => 0,
			'form_id'    => $this->id,
			'vendor_id'  => get_current_user_id(),
			'action' => 'fes-submit-' . $this->name() . '-form',
		);
		$default[ 'action' ] = fes_dash_to_lower( $default[ 'action' ] );
		return $default;
	}

}
