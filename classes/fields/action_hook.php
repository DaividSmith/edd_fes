<?php
class FES_Action_Hook_Field extends FES_Field {
	/** @var string Version of field */
	public $version = '1.0.0';	
	
	/** @var bool For 3rd parameter of get_post/user_meta */
	public $single = true;

	/** @var array Supports are things that are the same for all fields of a field type. Like whether or not a field type supports jQuery Phoenix. Stored in obj, not db. */
	public $supports = array(
		'multiple'    => true, // You can have multiples of this field
		'is_meta'     => true,  // in object as public (bool) $meta;
		'forms'       => array( // the forms you can use this field on
			'registration'     => true,
			'submission'       => true,
			'vendor-contact'   => true,
			'profile'          => true,
			'login'            => true,
		),
		'position'    => 'custom', // the position on the formbuilder
		'permissions' => array(
			'can_remove_from_formbuilder' => true, // this field can be removed once inserted into the formbuilder
			'can_change_meta_key'         => true, // you can change the meta key this field saves to in the formbuilder
			'can_add_to_formbuilder'      => true, // you can add this field to a form via the formbuilder
		),
		'template'    => 'action_hook', // the type of field
		'title'       => 'Action Hook',
		'phoenix'   => false, // whether this field will support jQuery Phoenix in 2.4
	);

	/** @var array Characteristics are things that can change from field to field of the same field type. Like the placeholder between two email fields. Stored in db. */
	public $characteristics = array(
		'name'        => '', // the metakey where this field saves to
		'template'    => 'action_hook',
		'public'      => false, // can you display this publicly (used by FES_Field->display_field() )
		'required'    => false, // is it a required field (default is false)
	);

	public function set_title() {
		$title = _x( 'Action Hook', 'FES Field title translation', 'edd_fes' );
		$title = apply_filters( 'fes_' . $this->name() . '_field_title', $title );
		$this->supports['title'] = $title;		
	}

	/** Returns the Action_Hook to render a field in admin */
	public function render_field_admin( $user_id = -2, $readonly = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $readonly === -2 ) {
			$readonly = $this->readonly;
		}

		$user_id   = apply_filters( 'fes_render_action_hook_field_user_id_admin', $user_id, $this->id );
		$readonly  = apply_filters( 'fes_render_action_hook_field_readonly_admin', $readonly, $user_id, $this->id );

		$output        = '';
		$output     .= sprintf( '<fieldset class="fes-el %1s %2s %3s">', $this->template(), $this->name(), $this->css() );
		ob_start();
		do_action( $this->name(), $this->form, $this->save_id, $this );
		$output .= ob_get_clean();
		$output .= '</fieldset>';
		return $output;
	}

	/** Returns the Action_Hook to render a field in frontend */
	public function render_field_frontend( $user_id = -2, $readonly = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $readonly === -2 ) {
			$readonly = $this->readonly;
		}
		$user_id   = apply_filters( 'fes_render_action_hook_field_user_id_frontend', $user_id, $this->id );
		$readonly  = apply_filters( 'fes_render_action_hook_field_readonly_frontend', $readonly, $user_id, $this->id );

		$output        = '';
		$output     .= sprintf( '<fieldset class="fes-el %1s %2s %3s">', $this->template(), $this->name(), $this->css() );
		ob_start();
		do_action( $this->name(), $this->form, $this->save_id, $this );
		$output .= ob_get_clean();
		$output .= '</fieldset>';
		return $output;
	}

	/** Returns the Action_Hook to render a field for the formbuilder */
	public function render_formbuilder_field( $index = -2, $insert = false ) {
		$removable    = $this->can_remove_from_formbuilder();
		$title_name   = sprintf( '%s[%d][name]', 'fes_input', $index );
		$title_value  = esc_attr( $this->name() );
		ob_start(); ?>
		<li class="action_hook">
			<?php $this->legend( $this->title(), $this->name(), $removable ); ?>
			<?php FES_Formbuilder_Templates::public_radio( $index, $this->characteristics, $this->form_name, true ); ?>
			<?php FES_Formbuilder_Templates::hidden_field( "[$index][template]", $this->template() ); ?>

			<?php FES_Formbuilder_Templates::field_div( $index, $this->name(), $this->characteristics, $insert ); ?>
				<div class="fes-form-rows">
					<label><?php _e( 'Hook Name', 'edd_fes' ); ?></label>
					<div class="fes-form-sub-fields">
						<input type="text" class="smallipopInput" title="<?php _e( 'Name of the hook', 'edd_fes' ); ?>" name="<?php echo esc_html( $title_name ); ?>" value="<?php echo esc_attr( $title_value ); ?>" />

						<div class="description" style="margin-top: 8px;">
							<?php _e( "This is for developers to add dynamic elements as they want. It provides the chance to add whatever input type you want to add in this form.", 'edd_fes' ); ?>
							<?php _e( 'You can bind your own functions to render the form to this action hook. You\'ll be given 3 parameters to play with: $form_id, $post_id, $form_settings.', 'edd_fes' ); ?>
							<pre>
add_action('{hookname}', 'my_function_name}', 10, 3 );
// first param: Form Object
// second param: Save ID of post/user/custom
// third param: Field Object
function my_function_name( $form, $save_id, $field ) {
	// Do whatever you want here
}
							</pre>
						</div>
					</div>
				</div>
			</div>
		</li>
		<?php
		return ob_get_clean();
	}

	// note in order for this to run, a hidden text field should be output in the render function with an id of the meta_key, else this won't run
	public function save_field_admin( $save_id = -2, $value = '', $user_id = -2 ) {
		do_action( $this->name() . '_save_admin', $save_id, $value, $user_id, $this );
	}

	// note in order for this to run, a hidden text field should be output in the render function with an id of the meta_key, else this won't run
	public function save_field_frontend( $save_id = -2, $value = '', $user_id = -2 ) {
		do_action( $this->name() . '_save_frontend', $save_id, $value, $user_id, $this );
	}	

	/** Returns the HTML to a public field in frontend */
	public function display_field( $user_id = -2, $single = false ) {
		return apply_filters( 'fes_display_' . $this->template() . '_field', '', $user_id, $single );
	}

	/** Returns formatted data of field in frontend */
	public function formatted_data( $user_id = -2 ) {
		return apply_filters( 'fes_formatted_' . $this->template() . '_field', '', $user_id );
	}	

	public function validate( $values = array(), $save_id = -2, $user_id = -2 ) {
		return apply_filters( 'fes_validate_' . $this->template() . '_field', false, $values,  $this->name(), $save_id, $user_id );
	}

	public function sanitize( $values = array(), $save_id = -2, $user_id = -2 ) {
		return apply_filters( 'fes_sanitize_' . $this->template() . '_field', $values, $this->name(), $save_id, $user_id );
	}
}
