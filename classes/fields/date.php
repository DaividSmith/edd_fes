<?php
class FES_Date_Field extends FES_Field {
	/** @var string Version of field */
	public $version = '1.0.0';	
	
	/** @var bool For 3rd parameter of get_post/user_meta */
	public $single = true;

	/** @var array Supports are things that are the same for all fields of a field type. Like whether or not a field type supports jQuery Phoenix. Stored in obj, not db. */
	public $supports = array(
		'multiple'    => true,
		'is_meta'     => true,  // in object as public (bool) $meta;
		'forms'       => array(
			'registration'     => true,
			'submission'       => true,
			'vendor-contact'   => false,
			'profile'          => true,
			'login'            => false,
		),
		'position'    => 'custom',
		'permissions' => array(
			'can_remove_from_formbuilder' => true,
			'can_change_meta_key'         => true,
			'can_add_to_formbuilder'      => true,
		),
		'template'    => 'date',
		'title'       => 'Date',
		'phoenix'     => true,
	);

	/** @var array Characteristics are things that can change from field to field of the same field type. Like the placeholder between two email fields. Stored in db. */
	public $characteristics = array(
		'name'        => '',
		'template'    => 'date',
		'public'      => true,
		'required'    => false,
		'label'       => '',
		'format'    => 'mm/dd/yy',
		'time'        => 'no',
	);


	public function set_title() {
		$title = _x( 'Date', 'FES Field title translation', 'edd_fes' );
		$title = apply_filters( 'fes_' . $this->name() . '_field_title', $title );
		$this->supports['title'] = $title;		
	}

	/** Returns the Date to render a field in admin */
	public function render_field_admin( $user_id = -2, $readonly = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $readonly === -2 ) {
			$readonly = $this->readonly;
		}
		$user_id   = apply_filters( 'fes_render_date_field_user_id_admin', $user_id, $this->id );
		$readonly  = apply_filters( 'fes_render_date_field_readonly_admin', $readonly, $user_id, $this->id );
		$value     = $this->get_field_value_frontend( $this->save_id, $user_id, $readonly );

		$output        = '';
		$output     .= sprintf( '<fieldset class="fes-el %1s %2s %3s">', $this->template(), $this->name(), $this->css() );
		$output    .= $this->label( $readonly );
		ob_start(); ?>
		<div class="fes-fields">
			<input id="<?php echo $this->name(); ?>" type="text" class="datepicker" data-required="false" data-type="text" name="<?php echo esc_attr( $this->name() ); ?>" value="<?php echo esc_attr( $value ) ?>" size="30" />
		</div>
		<script type="text/javascript">
			jQuery(function($) {
			<?php if ( $this->characteristics['time'] == 'yes' ) { ?>
				$("#<?php echo $this->name(); ?>").datetimepicker({ dateFormat: '<?php echo $this->characteristics['format']; ?>' });
			<?php } else { ?>
				$("#<?php echo $this->name(); ?>").datepicker({ dateFormat: '<?php echo $this->characteristics['format']; ?>' });
			<?php } ?>
			});
		</script>
		<?php
		$output .= ob_get_clean();
		$output .= '</fieldset>';
		return $output;
	}

	/** Returns the Date to render a field in frontend */
	public function render_field_frontend( $user_id = -2, $readonly = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $readonly === -2 ) {
			$readonly = $this->readonly;
		}
		$user_id   = apply_filters( 'fes_render_date_field_user_id_frontend', $user_id, $this->id );
		$readonly  = apply_filters( 'fes_render_date_field_readonly_frontend', $readonly, $user_id, $this->id );
		$value     = $this->get_field_value_frontend( $this->save_id, $user_id, $readonly );
		$required  = $this->required( $readonly );
		$output        = '';
		$output     .= sprintf( '<fieldset class="fes-el %1s %2s %3s">', $this->template(), $this->name(), $this->css() );
		$output    .= $this->label( $readonly );
		ob_start(); ?>
		<div class="fes-fields">
			<input id="<?php echo $this->name(); ?>" type="text" class="datepicker" data-required="<?php echo $required; ?>" data-type="text"<?php $this->required_html5( $readonly ); ?> name="<?php echo esc_attr( $this->name() ); ?>" value="<?php echo esc_attr( $value ) ?>" size="30" />
		</div>
		<script type="text/javascript">
			jQuery(function($) {
			<?php if ( $this->characteristics['time'] == 'yes' ) { ?>
				$("#<?php echo $this->name(); ?>").datetimepicker({ dateFormat: '<?php echo $this->characteristics['format']; ?>' });
			<?php } else { ?>
				$("#<?php echo $this->name(); ?>").datepicker({ dateFormat: '<?php echo $this->characteristics['format']; ?>' });
			<?php } ?>
			});
		</script>
		<?php
		$output .= ob_get_clean();
		$output .= '</fieldset>';
		return $output;
	}

	/** Returns the Date to render a field for the formbuilder */
	public function render_formbuilder_field( $index = -2, $insert = false ) {
		$removable    = $this->can_remove_from_formbuilder();
		$format_name  = sprintf( '%s[%d][format]', 'fes_input', $index );
		$time_name    = sprintf( '%s[%d][time]', 'fes_input', $index );
		$format_value = $this->characteristics['format'];
		$time_value   = $this->characteristics['time'];
		$help         = esc_attr( __( 'The date format', 'edd_fes' ) ); ?>
		<li class="custom-field custom_image">
			<?php $this->legend( $this->title(), $this->get_label(), $removable ); ?>
			<?php FES_Formbuilder_Templates::hidden_field( "[$index][template]", $this->template() ); ?>

			<?php FES_Formbuilder_Templates::field_div( $index, $this->name(), $this->characteristics, $insert ); ?>
				<?php FES_Formbuilder_Templates::public_radio( $index, $this->characteristics, $this->form_name ); ?>
				<?php FES_Formbuilder_Templates::standard( $index, $this ); ?>

				<div class="fes-form-rows">
					<label><?php _e( 'Date Format', 'edd_fes' ); ?></label>
					<input type="text" class="smallipopInput" name="<?php echo $format_name; ?>" value="<?php echo $format_value; ?>" title="<?php echo $help; ?>">
				</div>

				<div class="fes-form-rows">
					<label><?php _e( 'Time', 'edd_fes' ); ?></label>

					<div class="fes-form-sub-fields">
						<label>
							<?php FES_Formbuilder_Templates::hidden_field( "[$index][time]", 'no' ); ?>
							<input type="checkbox" name="<?php echo $time_name ?>" value="yes"<?php checked( $time_value, 'yes' ); ?> />
							<?php _e( 'Enable time input', 'edd_fes' ); ?>
						</label>
					</div>
				</div>
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
			$values[ $name ] = trim( $values[ $name ] );
		}
		return apply_filters( 'fes_sanitize_' . $this->template() . '_field', $values, $name, $save_id, $user_id );
	}
}
