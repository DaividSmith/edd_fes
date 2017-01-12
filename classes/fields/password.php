<?php
class FES_Password_Field extends FES_Field {
	/** @var string Version of field */
	public $version = '1.0.0';	
	
	/** @var bool For 3rd parameter of get_post/user_meta */
	public $single = true;

	/** @var array Supports are things that are the same for all fields of a field type. Like whether or not a field type supports jQuery Phoenix. Stored in obj, not db. */
	public $supports = array(
		'multiple'    => false,
		'is_meta'     => false,  // in object as public (bool) $meta;
		'forms'       => array(
			'registration'     => true,
			'submission'       => false,
			'vendor-contact'   => false,
			'profile'          => true,
			'login'            => true,
		),
		'position'    => 'specific',
		'permissions' => array(
			'can_remove_from_formbuilder' => true,
			'can_change_meta_key'         => true,
			'can_add_to_formbuilder'      => true,
		),
		'template'   => 'password',
		'title'       => 'Password',
		'phoenix'    => false,
	);

	/** @var array Characteristics are things that can change from field to field of the same field type. Like the placeholder between two email fields. Stored in db. */
	public $characteristics = array(
		'name'        => 'user_pass',
		'public'	  => false,
		'template'   => 'password',
		'public'      => false,
		'required'    => true,
		'label'       => '',
		'show_placeholder' => false,
		'default'          => false,
		'min_length'     => '12',
		'repeat_pass'     => 'no',
		're_pass_label'   => 'Confirm Password',
	);

	public function set_title() {
		$title = _x( 'Password', 'FES Field title translation', 'edd_fes' );
		$title = apply_filters( 'fes_' . $this->name() . '_field_title', $title );
		$this->supports['title'] = $title;		
	}

	/** Returns the Password to render a field in admin */
	public function render_field_admin( $user_id = -2, $readonly = -2 ) {
		return '';
	}

	/** Returns the Password to render a field in frontend */
	public function render_field_frontend( $user_id = -2, $readonly = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $readonly === -2 ) {
			$readonly = $this->readonly;
		}

		$repeat        = !empty( $this->characteristics['repeat_pass'] ) ? $this->characteristics['repeat_pass'] : 'no';
		$required      = $this->required( $readonly );
		$output        = '';
		$output     .= sprintf( '<fieldset class="fes-el %1s %2s %3s">', $this->template(), $this->name(), $this->css() );
		$output        .= $this->label( $readonly );
		if ( $readonly ) {
			return '';
		}
		ob_start(); ?>
		<div class="fes-fields">
			<input id="user_pass" type="password" class="password textfield<?php echo $this->required_class( $readonly ); ?>" data-required="<?php echo $required; ?>" data-type="text"<?php $this->required_html5( $readonly ); ?> name="user_pass" value="" />
		</div>

		<?php
		if ( $repeat == 'yes' ) {
			echo $this->repeat_label( $readonly ); ?>
			<div class="fes-fields">
				<input id="pass2" type="password" class="password textfield<?php echo $this->required_class( $readonly ); ?>" data-required="<?php echo $required; ?>" data-type="text"<?php $this->required_html5( $readonly ); ?> name="pass2" value="" />
			</div>
			<?php
		}
		$output .= ob_get_clean();
		$output .= '</fieldset>';
		return $output;
	}

	public function repeat_label( $readonly ) {
		$name  = 'pass2';
		$label = !empty( $this->characteristics['re_pass_label'] ) ? $this->characteristics['re_pass_label'] : __( 'Confirm Password', 'edd_fes' );
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

	/** Returns the Password to render a field for the formbuilder */
	public function render_formbuilder_field( $index = -2, $insert = false ) {
		global $post;
		$removable           = $this->can_remove_from_formbuilder();
		if ( is_object( $post ) && get_the_ID() == EDD_FES()->helper->get_option( 'fes-registration-form', false )  ) {
			$removable = false;
		}

		$min_length_name     = sprintf( '%s[%d][min_length]', 'fes_input', $index );
		$pass_repeat_name    = sprintf( '%s[%d][repeat_pass]', 'fes_input', $index );
		$re_pass_label       = sprintf( '%s[%d][re_pass_label]', 'fes_input', $index );
		$min_length_value    = isset( $this->characteristics['min_length'] ) ? esc_attr( $this->characteristics['min_length'] ) : '6';
		$pass_repeat_value   = isset( $this->characteristics['repeat_pass'] ) ? esc_attr( $this->characteristics['repeat_pass'] ) : 'no';
		$re_pass_label_value = isset( $this->characteristics['re_pass_label'] ) ? esc_attr( $this->characteristics['re_pass_label'] ) : '';
		$public 			 = isset( $this->characteristics['public'] ) ? esc_attr( $this->characteristics['public'] ) : false;
		ob_start(); ?>
		<li class="password">
			<?php $this->legend( $this->title(), $this->get_label(), $removable ); ?>
			<?php FES_Formbuilder_Templates::hidden_field( "[$index][template]", $this->template() ); ?>
			<?php FES_Formbuilder_Templates::field_div( $index, $this->name(), $this->characteristics, $insert ); ?>
			<?php FES_Formbuilder_Templates::standard( $index, $this ); ?>
			<?php FES_Formbuilder_Templates::common_text( $index, $this->characteristics ); ?>
				<div class="fes-form-rows">
					<label><?php _e( 'Minimum password length', 'edd_fes' ); ?></label>
					<input type="text" name="<?php echo $min_length_name ?>" value="<?php echo esc_attr( $min_length_value ); ?>" />
				</div> <!-- .fes-form-rows -->

				<div class="fes-form-rows">
					<label><?php _e( 'Password Re-type', 'edd_fes' ); ?></label>

					<div class="fes-form-sub-fields">
						<label>
							<?php FES_Formbuilder_Templates::hidden_field( "[$index][repeat_pass]", 'no' ); ?>
							<input class="retype-pass" type="checkbox" name="<?php echo $pass_repeat_name ?>" value="yes" <?php checked( $pass_repeat_value, 'yes' ); ?> />
							<?php _e( 'Require Password repeat', 'edd_fes' ); ?>
						</label>
					</div>
				</div> <!-- .fes-form-rows -->

				<div class="fes-form-rows<?php echo $pass_repeat_value != 'yes' ? ' fes-hide' : ''; ?>">
					<label><?php _e( 'Re-type password label', 'edd_fes' ); ?></label>

					<input type="text" name="<?php echo $re_pass_label ?>" value="<?php echo esc_attr( $re_pass_label_value ); ?>" />
				</div> <!-- .fes-form-rows -->
			</div> <!-- .fes-form-holder -->
		</li>
		<?php
		return ob_get_clean();
	}

	public function validate( $values = array(), $save_id = -2, $user_id = -2 ) {
		$name = $this->name();
		$return_value = false;
		if ( !empty( $values[ $name ] ) ) {
			$pass_repeat_value   = isset( $this->characteristics['repeat_pass'] ) ? $this->characteristics['repeat_pass']: 'no';
			$min_length_value    = isset( $this->characteristics['min_length'] ) ? $this->characteristics['min_length'] : '6';
			if ( $pass_repeat_value === 'yes' ) {
				if ( empty( $values[ 'pass2' ] ) ) {
					$return_value = __( 'Please fill in the repeat password field', 'edd_fes' );
				}

				if ( $values[ 'pass2' ] !== $values[ 'user_pass' ] ) {
					$return_value = __( 'First password doesn\'t match the second one', 'edd_fes' );
				}
			}

			if ( strlen( $values[ 'user_pass' ] ) < intval( $min_length_value ) ) {
				$return_value = sprintf( __( 'Passwords must be at least %d characters long', 'edd_fes' ), intval( $min_length_value ) );
			}
		} else {
			if ( $this->form_name == 'profile' ) {
				// profile forms don't require this form
			} else {
				// if required but isn't present
				if ( $this->required() ) {
					$return_value = __( 'Please fill out this field.', 'edd_fes' );
				}
			}
		}
		return apply_filters( 'fes_validate_' . $this->template() . '_field', $return_value, $values, $name, $save_id, $user_id );
	}

	public function sanitize( $values = array(), $save_id = -2, $user_id = -2 ) {
		$name = $this->name();
		if ( !empty( $values[ $name ] ) ) {
			$values[ $name ] = trim( $values[ $name ] );
			$values[ $name ] = sanitize_text_field( $values[ $name ] );
		}

		// if form === profile && password is not required
			// unset is not set
		if ( $this->form_name == 'profile' ){
			$name = $this->name();
			if ( empty( $values[ $name ] ) ) {
				if ( isset( $values[ $name ] ) ) {
					unset( $values[ $name ] );
				}
				if ( isset( $values[ "password" ] ) ) {
					unset( $values[ "password" ] );
				}
				add_filter( 'fes_templates_to_exclude_save_' . $this->form_name . '_form_frontend', array( $this, 'fes_templates_to_exclude' ) );
			}
		}

		return apply_filters( 'fes_sanitize_' . $this->template() . '_field', $values, $name, $save_id, $user_id );
	}

	public function fes_templates_to_exclude( $templates ) {
		array_push( $templates, 'password' );
		return $templates;
	}	
}
