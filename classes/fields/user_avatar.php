<?php
class FES_User_Avatar_Field extends FES_Field {
	/** @var string Version of field */
	public $version = '1.0.0';	
	
	/** @var bool For 3rd parameter of get_post/user_meta */
	public $single = true;

	/** @var array Supports are things that are the same for all fields of a field type. Like whether or not a field type supports jQuery Phoenix. Stored in obj, not db. */
	public $supports = array(
		'multiple'    => false,
		'is_meta'     => true,  // in object as public (bool) $meta;
		'forms'       => array(
			'registration'     => false,
			'submission'       => false,
			'vendor-contact'   => false,
			'profile'          => true,
			'login'            => false,
		),
		'position'    => 'specific',
		'permissions' => array(
			'can_remove_from_formbuilder' => true,
			'can_change_meta_key'         => false,
			'can_add_to_formbuilder'      => true,
		),
		'template'   => 'user_avatar',
		'title'       => 'User Avatar',
		'phoenix'    => true,
	);

	/** @var array Characteristics are things that can change from field to field of the same field type. Like the placeholder between two email fields. Stored in db. */
	public $characteristics = array(
		'name'        => 'user_avatar',
		'template'   => 'user_avatar',
		'public'      => true,
		'required'    => true,
		'label'       => '',
		'count '      => '1',
	);

	public function set_title() {
		$title = _x( 'User Avatar', 'FES Field title translation', 'edd_fes' );
		$title = apply_filters( 'fes_' . $this->name() . '_field_title', $title );
		$this->supports['title'] = $title;		
	}

	public function extending_constructor( ) {
		// No extending constructor filters
	}

	public function exclude_field( $fields ) {
		array_push( $fields, 'user_avatar' );
		return $fields;
	}

	/** Returns the User_Avatar to render a field in admin */
	public function render_field_admin( $user_id = -2, $readonly = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $readonly === -2 ) {
			$readonly = $this->readonly;
		}
		$user_id   = apply_filters( 'fes_render_user_avatar_field_user_id_admin', $user_id, $this->id );
		$readonly  = apply_filters( 'fes_render_user_avatar_field_readonly_admin', $readonly, $user_id, $this->id );
		$value     = $this->get_field_value_frontend( $this->save_id, $user_id, $readonly );

		$avatar_id = fes_get_attachment_id_from_url( $value );
		$required  = $this->required( $readonly );

		$output        = '';
		$output     .= sprintf( '<fieldset class="fes-el %1s %2s %3s">', $this->template(), $this->name(), $this->css() );
		$output    .= $this->label( $readonly );
		ob_start(); ?>
				<div class="fes-fields">
						<div class="fes-avatar-image-upload">
								<div class="instruction-inside <?php if ( ! empty( $avatar_id ) ) { echo 'fes-hide'; } ?>">
										<input type="hidden" name="<?php echo $this->name(); ?>" class="fes-avatar-image-id" value="<?php echo esc_attr( $avatar_id ); ?>">
										<a href="#" class="fes-avatar-image-btn edd-submit button"><?php _e( 'Upload Avatar', 'edd_fes' ); ?></a>
								</div>

									<div class="image-wrap <?php if ( empty( $avatar_id ) ) { echo 'fes-hide'; } ?>">
										<a class="close fes-remove-avatar-image">&times;</a>
										<img src="<?php echo esc_attr( $value ); ?>" alt="" class="fes-avatar-image">
								</div>
						</div>
				</div> <!-- .fes-fields -->
				<?php
		$output .= ob_get_clean();
		$output .= '</fieldset>';
		return $output;
	}

	/** Returns the User_Avatar to render a field in frontend */
	public function render_field_frontend( $user_id = -2, $readonly = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $readonly === -2 ) {
			$readonly = $this->readonly;
		}
		$user_id   = apply_filters( 'fes_render_user_avatar_field_user_id_frontend', $user_id, $this->id );
		$readonly  = apply_filters( 'fes_render_user_avatar_field_readonly_frontend', $readonly, $user_id, $this->id );
		$value     = $this->get_field_value_frontend( $this->save_id, $user_id, $readonly );

		$avatar_id = fes_get_attachment_id_from_url( $value );
		$required  = $this->required( $readonly );

		$output        = '';
		$output     .= sprintf( '<fieldset class="fes-el %1s %2s %3s">', $this->template(), $this->name(), $this->css() );
		$output    .= $this->label( $readonly );
		ob_start(); ?>
				<div class="fes-fields">
						<div class="fes-avatar-image-upload">
								<div class="instruction-inside <?php if ( ! empty( $avatar_id ) ) { echo 'fes-hide'; } ?>">
										<input type="hidden" name="<?php echo $this->name(); ?>" class="fes-avatar-image-id" value="<?php echo esc_attr( $avatar_id ); ?>">
										<a href="#" class="fes-avatar-image-btn edd-submit button"><?php _e( 'Upload Avatar', 'edd_fes' ); ?></a>
								</div>

									<div class="image-wrap <?php if ( empty( $avatar_id ) ) { echo 'fes-hide'; } ?>">
										<a class="close fes-remove-avatar-image">&times;</a>
										<img src="<?php echo esc_attr( $value ); ?>" alt="" class="fes-avatar-image">
								</div>
						</div>
				</div> <!-- .fes-fields -->
				<?php
		$output .= ob_get_clean();
		$output .= '</fieldset>';
		return $output;
	}

	/** Returns the User_Avatar to render a field for the formbuilder */
	public function render_formbuilder_field( $index = -2, $insert = false ) {
		$removable = $this->can_remove_from_formbuilder(); ?>
		<li class="user_avatar">
			<?php $this->legend( $this->title(), $this->get_label(), $removable ); ?>
			<?php FES_Formbuilder_Templates::hidden_field( "[$index][template]", $this->template() ); ?>
			<?php FES_Formbuilder_Templates::hidden_field( "[$index][count]", '1' ); ?>

			<?php FES_Formbuilder_Templates::field_div( $index, $this->name(), $this->characteristics, $insert ); ?>
					<?php FES_Formbuilder_Templates::public_radio( $index, $this->characteristics, $this->form_name ); ?>
					<?php FES_Formbuilder_Templates::standard( $index, $this ); ?>
			</div>
		</li>
		<?php
		return ob_get_clean();
	}

	public function validate( $values = array(), $save_id = -2, $user_id = -2 ) {
		$name = $this->name();
		$return_value = false;
		if ( !empty( $values[ $name ] ) ) {
			// if the value is set

		} else {
			// if required but isn't present
			if ( $this->required() ) {
				$return_value = __( 'Please fill out this field.', 'edd_fes' );
			}
		}
		return apply_filters( 'fes_validate_' . $this->template() . '_field', $return_value, $values, $name, $save_id, $user_id );
	}

	public function sanitize( $values = array(), $save_id = -2, $user_id = -2 ) {
		$name = $this->name();
		if ( !empty( $values[ $name ] ) ) {
			$values[ $name ] = intval( trim( $values[ $name ] ) );
		} else {
			$values[ $name ] = '';
		}
		return apply_filters( 'fes_sanitize_' . $this->template() . '_field', $values, $name, $save_id, $user_id );
	}

	public function save_field_admin( $save_id = -2, $value = '', $user_id = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $save_id == -2 ) {
			$save_id = $this->save_id;
		}

		$user_id  = apply_filters( 'fes_save_field_user_id_admin', $user_id, $save_id, $value );
		$value    = apply_filters( 'fes_save_field_value_admin', $value, $save_id, $user_id );

		do_action( 'fes_save_field_before_save_admin', $save_id, $value, $user_id );

		if ( ! empty( $value ) ) {
			$attachment_id = absint( $value );
			fes_update_avatar( $save_id, $attachment_id );
		} else {
			delete_user_meta( $save_id, 'user_avatar' );
		}

		$this->value = $value;
		do_action( 'fes_save_field_after_save_admin', $save_id, $value, $user_id );
	}

	public function save_field_frontend( $save_id = -2, $value = '', $user_id = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $save_id == -2 ) {
			$save_id = $this->save_id;
		}

		$user_id  = apply_filters( 'fes_save_field_user_id_frontend', $user_id, $save_id, $value );
		$value    = apply_filters( 'fes_save_field_value_frontend', $value, $save_id, $user_id );

		do_action( 'fes_save_field_before_save_frontend', $save_id, $value, $user_id );

		if ( ! empty( $value ) ) {
			$attachment_id = absint( $value );
			fes_update_avatar( $save_id, $attachment_id );
		} else {
			delete_user_meta( $save_id, 'user_avatar' );
		}

		$this->value = $value;
		do_action( 'fes_save_field_after_save_frontend', $save_id, $value, $user_id );
	}

	/** Gets field value for admin */
	public function get_field_value_admin( $save_id = -2, $user_id = -2, $public = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $public === -2 ) {
			$public  = $this->readonly;
		}

		$public   = apply_filters( 'fes_get_field_value_public_admin', $public , $this->id, $user_id );
		$user_id  = apply_filters( 'fes_get_field_value_user_id_admin', $user_id, $this->id );
		$save_id  = apply_filters( 'fes_get_field_value_save_id_admin', $save_id, $this->id );

		if ( $save_id === -2 ) {
			// if the place we are saving to doesn't have a save_id, we are likely on a draft product or draft vendor and therefore don't have a value
			// if there's a default lets use that
			if ( isset( $this->characteristics ) && isset( $this->characteristics ) && isset( $this->characteristics['default'] ) ) {
				$value = $this->characteristics['default'];
			}
			$value = apply_filters( 'fes_get_field_value_early_value_admin', null, $save_id, $user_id, $public );
			return $value;
		}

		$avatar = get_avatar( $save_id );
		preg_match( "/src=['\"](.*?)['\"]/i", $avatar, $value );
		$value = $value[1];

		if ( substr( $value, 0, 4 ) === "data" ) {
			$value = ''; // grr gravatar
		}

		$value = apply_filters( 'fes_get_field_value_return_value_admin', $value, $save_id, $user_id, $public  );
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

		$public   = apply_filters( 'fes_get_field_value_public_frontend', $public , $this->id, $user_id );
		$user_id  = apply_filters( 'fes_get_field_value_user_id_frontend', $user_id, $this->id );
		$save_id  = apply_filters( 'fes_get_field_value_save_id_frontend', $save_id, $this->id );

		if ( $save_id === -2 ) {
			// if the place we are saving to doesn't have a save_id, we are likely on a draft product or draft vendor and therefore don't have a value
			// if there's a default lets use that
			if ( isset( $this->characteristics ) && isset( $this->characteristics ) && isset( $this->characteristics['default'] ) ) {
				$value = $this->characteristics['default'];
			}
			$value = apply_filters( 'fes_get_field_value_early_value_frontend', null, $save_id, $user_id, $public );
			return $value;
		}

		$avatar = get_avatar( $save_id );
		preg_match( "/src=['\"](.*?)['\"]/i", $avatar, $value );
		$value = $value[1];

		if ( substr( $value, 0, 4 ) === "data" ) {
			$value = ''; // grr gravatar
		}

		$value = apply_filters( 'fes_get_field_value_return_value_frontend', $value, $save_id, $user_id, $public  );
		return $value;
	}
}
