<?php
class FES_Field {

	/** @var string The field ID. */
	public $id = null;

	/** @var string Version of field */
	public $version = '1.0.0';		

	/** @var unknown Value of the field */
	public $value = null;

	/** @var int The form id the field appears on. */
	public $form = null;

	/** @var string The form's name. */
	public $form_name = null;

	/** @var string The type of form */
	public $type = null;

	/** @var int The id of the object the field value is saved to. For a usermeta field, the $save_id is the user's ID. For a postmeta field, the $save_id is the post's ID, etc. */
	public $save_id = null;

	/** @var bool True for post/usermeta. False for inherit. Use true if you want to save a field somewhere custom, and then hook into save_field */
	public $meta = true;

	/** @var bool For 3rd parameter of get_post/user_meta */
	public $single = false;

	/** @var bool Is the field readonly? */
	public $readonly = false;

	/** @var array Supports are things that are the same for all fields of a field type. Like whether or not a field type supports jQuery Phoenix. Stored in obj, not db. */
	public $supports = array(
		'multiple'    => true,
		'forms'       => array( // forms this field supports
			'registration'     => true,
			'submission'       => true,
			'vendor-contact'   => true,
			'profile'          => true,
			'login'            => true,
		),
		'position'    => 'custom', // where the button to add this appears on the formbuilder. Top right = "specific", middle = "custom", bottom = "extension". Extensions should register on extension
		'permissions' => array(
			'can_remove_from_formbuilder' => true,
			'can_change_meta_key'         => true,
			'can_add_to_formbuilder'      => true,
		),
		'template'    => 'text',
		'title'       => 'Text',
		'phoenix'     => false,
	);

	/** @var array Characteristics are things that can change from field to field of the same field type. Like the placeholder between two text fields. Stored in db. */
	public $characteristics = array(
		'name'        => '',
		'template'    => 'text',
		'public'      => true,
		'required'    => false,
		'label'       => '',
		'name'        => '',
		'css'         => '',
		'default'     => '',
		'size'        => '',
		'help'        => '',
		'placeholder' => ''
	);

	/** From here down, parameters for functions as they relate to the field object are:
	 Function | Object   | Explanation
	 $field   | $name    | Usually this is the same as the meta_key for saving. This is the name of a field. Unique to each field.
	 $form    | $form    | $form is the int id of the form post that the field appears on
	 $type    | $type    | $type is the type of form the field is being used on (post, user, custom)
	 $save_id | $save_id | Corresponds to the ID of the object the field's value is saved to. See $save_id's parameter comment
	 */
	public function __construct( $field = '', $form = 'notset', $type = -2, $save_id = -2 ) {
		if ( is_array( $field ) ) {
			$this->id                 = isset( $field['name'] ) ? $field['name'] : $field;
			$this->characteristics    = $field;
			if ( $form != 'notset' ) {
				$this->form = $form;
				$this->form_name = get_post_meta( $form, 'fes-form-name', true );
			}
			if ( $type === -2 ) {
				if ( $form !== 'notset' ) {
					$type = EDD_FES()->helper->get_form_type_by_id( $form );
				} else {
					$type = 'custom';
				}
			}
			$this->type = $type;
			$this->save_id = $save_id;
			$this->meta = $this->is_meta();
			if ( is_numeric( $this->save_id ) ) {
				$this->value = $this->get_field_value();
			}
		} else if ( is_string( $field ) && strlen( $field ) > 0 ) {
			$this->id   = $field;
			if ( $form !== 'notset' ) {
				$this->form = $form;
				$this->form_name = get_post_meta( $form, 'fes-form-name', true );
				$this->characteristics = $this->pull_characteristics( $field, $form );
				$this->meta = $this->is_meta();
			}
			if ( $type === -2 ) {
				if ( $form !== 'notset' ) {
					$type = EDD_FES()->helper->get_form_type_by_id( $form );
				} else {
					$type = 'custom';
				}
			}
			$this->type = $type;
			$this->save_id = $save_id;
			if ( is_numeric( $this->save_id ) ) {
				$this->value = $this->get_field_value();
			}
		} else {
			$this->id   = $field;
			if ( $form != 'notset' ) {
				$this->form = $form;
				$this->form_name = get_post_meta( $form, 'fes-form-name', true );
			}
			if ( $type === -2 ) {
				if ( $form !== 'notset' ) {
					$type = EDD_FES()->helper->get_form_type_by_id( $form );
				} else {
					$type = 'custom';
				}
			}
			$this->type = $type;
			$this->save_id = $save_id;
			if ( is_numeric( $this->save_id ) ) {
				$this->value = $this->get_field_value();
			}
		}
		$this->set_title();
		$this->extending_constructor();
	}

	public function get_id() {
		return $this->id;
	}

	public function set_id( $value ) {
		$this->id = $value;
	}

	/** get_value pulls the value from the obj. It does not touch the db */
	public function get_value() {
		return $this->get_field_value();
	}

	/** set_value sets the value of the object. It does not save the value to the db */
	public function set_value( $value ) {
		$this->value = $value;
	}

	/** Aliases save_field */
	public function save_value( $value, $field, $form, $id ) {
		$this->save_field( $value, $field, $form, $id );
	}

	public function get_form() {
		return $this->form;
	}

	public function set_form( $form ) {
		$this->form = $form;
	}

	public function get_type() {
		return $this->type;
	}

	public function set_type( $type ) {
		$this->type = $type;
	}

	public function get_save_id() {
		return $this->save_id;
	}

	public function set_save_id() {
		$this->save_id = $save_id ;
	}

	public function set_meta( $meta ) {
		$this->meta = $meta;
	}

	public function get_supports() {
		return $this->supports;
	}

	public function set_supports( $supports ) {
		$this->supports = $supports;
	}

	public function add_supports( $supports ) {
		$this->supports = array_merge( $this->supports, $supports );
	}

	/** Gets characteristics from obj. Does not touch the db */
	public function get_characteristics() {
		return $this->characteristics;
	}

	/** Sets obj characteristics. Does not touch the db */
	public function set_characteristics( $characteristics ) {
		$this->characteristics = $characteristics;
	}

	/** Pulls the characteristics from the db, and sets the object value equal to that. Different than get_characteristics */
	public function pull_characteristics( $id = false, $form = false ) {
		if ( $id && $form ) {
			$this->id = $id;
			$this->form = $form;
		}
		$value;
		$fields = get_post_meta( $form, 'fes-form', true );
		if ( !$fields ) {
			$fields = array();
		}
		$found = false;
		foreach ( $fields as $field ) {
			if ( isset( $field['name'] ) && $field['name'] == $this->id ) {
				$value = $field;
				$found = true;
			}
		}

		if ( !$found ) {
			$value = $this->characteristics;
		}

		$value = apply_filters( 'fes_pull_field_characteristics', $value, $this );
		$this->characteristics = $value;
		return $value;
	}

	public function save_characteristics( $id = false, $form = false, $characteristics = array() ) {
		if ( $id && $form ) {
			$this->id = $id;
			$this->form = $form;
		}
		$fields = get_post_meta( $this->form, 'fes-form', true );
		foreach ( $fields as $field ) {
			if ( $field['name'] == $this->id ) {
				$field = $characteristics;
			}
		}
		update_post_meta( $this->form, 'fes-form', $fields );
		$this->characteristics = $characteristics;
	}

	/** Returns the HTML to render a field */
	public function render_field( $user_id = -2, $readonly = -2 ) {
		$output = '';
		if ( fes_is_admin() ) {
			$output .= $this->render_field_admin( $user_id, $readonly );
		} else {
			$output .= $this->render_field_frontend( $user_id, $readonly );
		}

		return $output;
	}

	/** Returns the HTML to render a field in admin */
	public function render_field_admin( $user_id = -2, $readonly = -2 ) {
		// defined in the extending fields
		return '';
	}

	/** Returns the HTML to render a field in frontend */
	public function render_field_frontend( $user_id = -2, $readonly = -2 ) {
		// defined in the extending fields
		return '';
	}

	/** Returns the HTML to a public field in frontend */
	public function display_field( $user_id = -2, $single = false ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}
		$user_id   = apply_filters( 'fes_display_' . $this->template() . '_field_user_id', $user_id, $this->id );
		$value     = $this->get_field_value_frontend( $this->save_id, $user_id );
		if ( $this->is_public() ) {
			ob_start(); ?>

			<?php if ( $single ) { ?>
			<table class="fes-display-field-table">
			<?php } ?>

			<tr class="fes-display-field-row <?php echo $this->template(); ?>" id="<?php echo $this->name(); ?>">
				<td class="fes-display-field-label"><?php echo $this->get_label(); ?></td>
				<td class="fes-display-field-values">
					<?php echo $value; ?>
				</td>
			</tr>

			<?php if ( $single ) { ?>
			</table>
			<?php } ?>
		<?php
		}
		return ob_get_clean();
	}

	/** Returns formatted data of field in frontend */
	public function formatted_data( $user_id = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		$user_id   = apply_filters( 'fes_formatted_' . $this->supports['template'] . '_field_user_id', $user_id, $this->id );
		$value     = $this->get_field_value_frontend( $this->save_id, $user_id );
		return $value;
	}

	/** Saves field by extracting value from array of values (for all fields of a form) */
	public function save_field_values( $save_id = -2, $values = array(), $user_id = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $save_id == -2 ) {
			$save_id = $this->save_id;
		}

		$user_id  = apply_filters( 'fes_save_field_values_user_id', $user_id, $save_id, $values, $user_id );
		$values  = apply_filters( 'fes_save_field_values_values', $values, $save_id, $values, $user_id );

		do_action( 'fes_save_field_values_before', $save_id, $values, $user_id );

		if ( isset( $values[ $this->name() ] ) ) {
			$this->save_field( $save_id, $values[ $this->name() ], $user_id );
		} else {
			$this->save_field( $save_id, '', $user_id );
		}
		do_action( 'fes_save_field_values_after', $save_id, $values, $user_id );
	}

	/** Saves field */
	public function save_field( $save_id = -2, $value = '', $user_id = -2 ) {
		if ( fes_is_admin() ) {
			$this->save_field_admin( $save_id, $value, $user_id );
		} else {
			$this->save_field_frontend( $save_id, $value, $user_id );
		}
	}

	/** Saves field in admin */
	public function save_field_admin( $save_id = -2, $value = '', $user_id = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $save_id == -2 ) {
			$save_id = $this->save_id;
		}

		$user_id  = apply_filters( 'fes_save_field_user_id_admin', $user_id, $this, $save_id, $value );
		$value    = apply_filters( 'fes_save_field_value_admin', $value, $this, $save_id, $user_id );

		do_action( 'fes_save_field_before_save_admin', $this, $save_id, $value, $user_id );
		if ( $this->type == 'post' ) {
			if ( (bool) $this->meta ) {
				$value = update_post_meta( $save_id, $this->id, $value );
			} else {
				// don't update non-meta in backend. Bad things happen. Seriously. Don't.
			}
		}
		else if ( $this->type == 'user' ) {
			if ( (bool) $this->meta ) {
				$value = update_user_meta( $save_id, $this->id, $value );
			} else {
				$arr = array();
				$arr['ID'] = $save_id;
				$arr[ $this->id ] = $value;
				wp_update_user( $arr );
			}
		} else if ( $this->type == 'custom' ) {
			$value = apply_filters( 'fes_save_field_custom_admin', $value, $this );
		} else {
			$value = apply_filters( 'fes_save_field_custom_admin', $value, $this );
		}

		$this->value = $value;
		do_action( 'fes_save_field_after_save_admin', $this, $save_id, $value, $user_id );
	}

	/** Saves field in frontend */
	public function save_field_frontend( $save_id = -2, $value = '', $user_id = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $save_id == -2 ) {
			$save_id = $this->save_id;
		}

		$user_id  = apply_filters( 'fes_save_field_user_id_frontend', $user_id, $this, $save_id, $value, $user_id );
		$value    = apply_filters( 'fes_save_field_value_frontend', $value, $this, $save_id, $value, $user_id );

		do_action( 'fes_save_field_before_save_frontend', $this, $save_id, $value, $user_id );

		if ( $this->type == 'post' ) {
			if ( (bool) $this->meta ) {
				$value = update_post_meta( $save_id, $this->id, $value );
			} else {
				$arr = array();
				$arr['ID'] = $save_id;
				$arr[ $this->id ] = $value;
				wp_update_post( $arr );
			}
		} else if ( $this->type == 'user' ) {
			if ( (bool) $this->meta ) {
				$value = update_user_meta( $save_id, $this->id, $value );
			} else {
				$arr = array();
				$arr['ID'] = $save_id;
				$arr[ $this->id ] = $value;
				wp_update_user( $arr );
			}
		} else if ( $this->type == 'custom' ) {
			$value = apply_filters( 'fes_save_field_custom_frontend', $value, $this );
		} else {
			$value = apply_filters( 'fes_save_field_custom_frontend', $value, $this );
		}

		$this->value = $value;
		do_action( 'fes_save_field_after_save_frontend', $this, $save_id, $value, $user_id );
	}

	/** Gets field value */
	public function get_field_value( $save_id = -2, $user_id = -2, $public = -2 ) {
		$value;
		if ( fes_is_admin() ) {
			$value = $this->get_field_value_admin( $save_id, $user_id, $public  );
		} else {
			$value = $this->get_field_value_frontend( $save_id, $user_id, $public );
		}
		return $value;
	}

	/** Gets field value for admin */
	public function get_field_value_admin( $save_id = -2, $user_id = -2, $public = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $public === -2 ) {
			$public  = $this->readonly;
		}

		$public   = apply_filters( 'fes_get_field_value_public_admin', $public, $this, $user_id );
		$user_id  = apply_filters( 'fes_get_field_value_user_id_admin', $user_id, $this );
		$save_id  = apply_filters( 'fes_get_field_value_save_id_admin', $save_id, $this);

		if ( $save_id === -2 ) {
			// if the place we are saving to doesn't have a save_id, we are likely on a draft product or draft vendor and therefore don't have a value
			// if there's a default lets use that
			if ( isset( $this->characteristics ) && isset( $this->characteristics ) && isset( $this->characteristics['default'] ) ) {
				$value = $this->characteristics['default'];
			}
			$value = apply_filters( 'fes_get_field_value_early_value_admin', null, $this, $save_id, $user_id, $public );
			return $value;
		}

		$value;

		if ( $this->type == 'post' ) {
			if ( (bool) $this->meta ) {
				$value = get_post_meta( $save_id, $this->id, $this->single );
			} else {
				$post  = get_post( $save_id );
				if ( $post ) {
					$param = $this->id;
					$value = $post->$param;
				}
			}
		} else if ( $this->type == 'user' ) {
			if ( (bool) $this->meta ) {
				$value = get_user_meta( $save_id, $this->id, $this->single );
			} else {
				$user  = get_userdata( $save_id );
				if ( $user && isset( $this->id ) ) {
					$param = $this->id;
					$value = $user->$param;
				}
			}
		} else if ( $this->type == 'custom' ) {
			$value = apply_filters( 'fes_get_field_value_custom_admin', null, $this );
		} else {
			$value = apply_filters( 'fes_get_field_value_custom_admin', null, $this );
		}

		$value = apply_filters( 'fes_get_field_value_return_value_admin', $value, $this, $save_id, $user_id, $public  );
		return $value;
	}

	/** Gets field value for frontend */
	public function get_field_value_frontend( $save_id = -2, $user_id = -2, $public = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $public === -2 ) {
			$public  = $this->readonly;
		}

		$public   = apply_filters( 'fes_get_field_value_public_frontend', $public, $this, $user_id );
		$user_id  = apply_filters( 'fes_get_field_value_user_id_frontend', $user_id, $this );
		$save_id  = apply_filters( 'fes_get_field_value_save_id_frontend', $save_id, $this );

		if ( $save_id === -2 ) {
			// if the place we are saving to doesn't have a save_id, we are likely on a draft product or draft vendor and therefore don't have a value
			// if there's a default lets use that
			if ( isset( $this->characteristics ) && isset( $this->characteristics ) && isset( $this->characteristics['default'] ) ) {
				$value = $this->characteristics['default'];
			}
			$value = apply_filters( 'fes_get_field_value_early_value_frontend', null, $this, $save_id, $user_id, $public );
			return $value;
		}

		$value = '';
		if ( $this->type == 'post' ) {
			if ( (bool) $this->meta ) {
				$value = get_post_meta( $save_id, $this->id, $this->single );
			} else {
				$post  = get_post( $save_id );
				if ( $post ) {
					$param = $this->id;
					$value = $post->$param;
				}
			}
		} else if ( $this->type == 'user' ) {
			if ( (bool) $this->meta ) {
				$value = get_user_meta( $save_id, $this->id, $this->single );
			} else {
				$user  = get_userdata( $save_id );
				if ( $user && isset( $this->id ) ) {
					$param = $this->id;
					$value = $user->$param;
				}
			}
		} else if ( $this->type == 'custom' ) {
			$value = apply_filters( 'fes_get_field_value_custom_frontend', null, $this );
		} else {
			$value = apply_filters( 'fes_get_field_value_custom_frontend', null, $this );
		}

		$value = apply_filters( 'fes_get_field_value_return_value_frontend', $value, $this, $save_id, $user_id, $public  );
		return $value;
	}

	/** Returns the HTML to render a field for the formbuilder */
	public function render_formbuilder_field( $index = -2, $insert = false ) {
		// defined in the extending fields
	}


	/** Validates field */
	public function validate( $values = array(), $save_id = -2, $user_id = -2 ) {
		if ( !empty( $values[ $name ] ) ) {
			// if the value is set
			// no specific validation
		} else {
			// if the field is required but isn't present
			if ( $this->required() ) {
				return __( 'Please fill out this field.', 'edd_fes' );
			}
		}
		return false;
	}

	/** Sanitizes field value */
	public function sanitize( $values = array(), $save_id = -2, $user_id = -2 ) {
		$name = $this->name();
		if ( !empty( $values[ $name ] ) ) {
			$values[ $name ] = trim( $values[ $name ] );
			$values[ $name ] = sanitize_text_field( $values[ $name ] );
		}
		return apply_filters( 'fes_sanitize_' . $this->template() . '_field', $values, $name, $save_id, $user_id );
	}

	public function required_mark( $readonly = false ) {
		if ( $this->required() && !$readonly ) {
			return apply_filters( 'fes_required_mark', '<span class="edd-required-indicator">*</span>' );
		}
	}

	public function required_html5( $readonly = false ) {
		if ( $this->required() && !$readonly ) {
			return apply_filters( 'fes_required_html5', ' required="required"' );
		}
	}

	public function required_class( $readonly = false ) {
		if ( $this->required() && !$readonly  ) {
			return apply_filters( 'fes_required_class', ' edd-required-indicator' );
		}
	}

	public function get_label( ) {
		return isset( $this->characteristics['label'] ) ?  $this->characteristics['label'] : '';
	}

	public function label( $readonly ) {
		$name  = $this->name();
		$label = $this->get_label();
		ob_start(); ?>
		<div class="fes-label">
			<label for="fes-<?php echo isset( $name ) ? $name : 'cls'; ?>"><?php echo $label . $this->required_mark( $readonly ); ?></label>
			<?php if ( $this->help() ) : ?>
			<span class="fes-help"><?php echo $this->help(); ?></span>
		  <?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	public function required( $readonly = false ) {
		$required = false;
		if ( ! $readonly ) {
			$required = isset( $this->characteristics['required'] ) ? $this->characteristics['required'] : 'no';
			if ( $required === 'no' ) {
				$required = false;
			}
			$required = (bool) $required;
		}
		$required = apply_filters( 'fes_' . $this->name() . '_field_required', $required, $this );
		return $required;
	}

	public function help() {
		return isset( $this->characteristics['help'] ) ?  $this->characteristics['help'] : '';
	}

	public function name() {
		return $this->characteristics['name'];
	}

	public function placeholder() {
		return isset( $this->characteristics['placeholder'] ) ?  $this->characteristics['placeholder'] : '';
	}

	public function size() {
		return isset( $this->characteristics['size'] ) ?  $this->characteristics['size'] : '';
	}

	public function template() {
		return $this->characteristics['template'];
	}

	public function set_title() {
		$title = _x( 'Text', 'FES Field title translation', 'edd_fes' );
		$title = apply_filters( 'fes_' . $this->name() . '_field_title', $title );
		$this->supports['title'] = $title;		
	}

	public function title() {
		$title = !empty( $this->supports[ 'title' ] ) ? $this->supports[ 'title' ] : 'Text';
		return $title;
	}

	public function css() {
		return isset( $this->characteristics['css'] ) ?  $this->characteristics['css'] : '';
	}

	public function can_remove_from_formbuilder() {
		return isset( $this->supports['permissions']['can_remove_from_formbuilder'] ) ? $this->supports['permissions']['can_remove_from_formbuilder'] : true;
	}

	public function is_meta() {
		if ( ( isset( $this->supports['is_meta'] ) && (bool) $this->supports['is_meta'] ) || (  ! isset( $this->supports['is_meta'] ) && isset( $this->characteristics['is_meta'] ) && (bool) $this->characteristics['is_meta'] ) ) {
			return true;
		} else {
			return false;
		}
	}

	public function is_public() {
		return isset( $this->characteristics['public'] ) ?  $this->characteristics['public'] : false;
	}

	public function get_meta( $object_id, $meta_key, $type = 'submission', $single = true ) {
		// this is deprecated. Use the get value functions. To be removed in 2.4.
		if ( !$object_id ) {
			return '';
		}

		if ( $type == 'submission' ||  $type == 'post' ) {
			return get_post_meta( $object_id, $meta_key, $single );
		}

		if ( $type == 'vendor-contact' ) {
			return get_user_meta( get_current_user_id(), $meta_key, $single );
		}

		return get_user_meta( $object_id, $meta_key, $single );
	}

	public function legend( $title = 'Field Type', $label = '', $removable = true ) {
		$legend      = '';
		$title       = $title;
		if ( $title === $label || $label === '' ) {
			$legend = '<strong>' . $title  . '</strong>';
		} else {
			$legend = '<strong>' . $title . '</strong>: '. $label;
		}

?>
		<div class="fes-legend" title="<?php _e( 'Click and Drag to rearrange', 'edd_fes' ); ?>">
			<div class="fes-label"><?php echo $legend; ?></div>
			<div class="fes-actions">
				<?php if ( $removable ) { ?>
				<a href="#" class="fes-remove"><?php _e( 'Remove', 'edd_fes' ); ?></a>
				<?php } ?>
				<a href="#" class="fes-toggle"><?php _e( 'Toggle', 'edd_fes' ); ?></a>
			</div>
		</div> <!-- .fes-legend -->
		<?php
	}

	public function extending_constructor( ) {
		// used by extending fields who need it
	}

}
