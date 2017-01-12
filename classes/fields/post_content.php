<?php
class FES_Post_Content_Field extends FES_Field {
	/** @var string Version of field */
	public $version = '1.0.0';	
	
	/** @var bool For 3rd parameter of get_post/user_meta */
	public $single = true;

	/** @var array Supports are things that are the same for all fields of a field type. Like whether or not a field type supports jQuery Phoenix. Stored in obj, not db. */
	public $supports = array(
		'multiple'    => false,
		'is_meta'     => false,  // in object as public (bool) $meta;
		'forms'       => array(
			'registration'     => false,
			'submission'       => true,
			'vendor-contact'   => false,
			'profile'          => false,
			'login'            => false,
		),
		'position'    => 'specific',
		'permissions' => array(
			'can_remove_from_formbuilder' => true,
			'can_change_meta_key'         => false,
			'can_add_to_formbuilder'      => true,
		),
		'template'   => 'post_content',
		'title'       => 'Description',
		'phoenix'    => true,
	);

	/** @var array Characteristics are things that can change from field to field of the same field type. Like the placeholder between two text fields. Stored in db. */
	public $characteristics = array(
		'name'        => 'post_content',
		'template'   => 'post_content',
		'public'      => false,
		'required'    => true,
		'label'       => '',
		'css'         => '',
		'default'     => '',
		'size'        => '',
		'help'        => '',
		'placeholder' => '',
		'cols'        => '50',
		'rows'        => '8',
		'rich'        => '',
		'insert_image'=> false,
	);

	public function set_title() {
		$title = _x( 'Description', 'FES Field title translation', 'edd_fes' );
		$title = apply_filters( 'fes_' . $this->name() . '_field_title', $title );
		$this->supports['title'] = $title;		
	}

	public function extending_constructor( ) {
		// exclude from submission form in admin
		add_filter( 'fes_templates_to_exclude_render_submission_form_admin', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'fes_templates_to_exclude_validate_submission_form_admin', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'fes_templates_to_exclude_save_submission_form_admin', array( $this, 'exclude_field' ), 10, 1  );
		add_action( 'fes_save_field_after_save_frontend', array( $this, 'extra_save_routine' ), 10, 3 );
	}

	public function exclude_field( $fields ) {
		array_push( $fields, 'post_content' );
		return $fields;
	}

	public function extra_save_routine( $save_id, $value, $user_id ) {
		// if there's images in the post content, attach them ot the post
		if ( $this->name() === 'post_content' ) {
			$dom = new DOMDocument();
			$value = get_post_field( 'post_content', $save_id, 'display' );
			if ( $value == '' ){
				return;
			}
			$dom->loadHTML( $value );
			$images = $dom->getElementsByTagName( 'img' );
			if ( $images->length ) {
				foreach ( $images as $img ) {
					$url           = $img->getAttribute( 'src' );
					$url           = str_replace( array(
							'"',
							"'",
							"\\"
						), '', $url );

					$author_id     = EDD_FES()->vendors->user_is_admin() ? 0 : $user_id;
					$attachment_id = fes_get_attachment_id_from_url( $url, $author_id );
					if ( $attachment_id ) {
						fes_associate_attachment( $attachment_id, $save_id );
					}
				}
			}
		}
	}

	/** Returns the HTML to render a field in admin */
	public function render_field_admin( $user_id = -2, $readonly = -2 ) {
		return '';
	}

	/** Returns the HTML to render a field in frontend */
	public function render_field_frontend( $user_id = -2, $readonly = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $readonly === -2 ) {
			$readonly = $this->readonly;
		}

		$user_id   = apply_filters( 'fes_render_post_content_field_user_id_frontend', $user_id, $this->id );
		$readonly  = apply_filters( 'fes_render_post_content_field_readonly_frontend', $readonly, $user_id, $this->id );
		$value     = $this->get_field_value_frontend( $this->save_id, $user_id, $readonly );
		$required  = $this->required( $readonly );
		$req_class = $required ? 'required' : 'rich-editor';
		$output        = '';
		$output     .= sprintf( '<fieldset class="fes-el %1s %2s %3s">', $this->template(), $this->name(), $this->css() );
		$output    .= $this->label( $readonly );
		ob_start(); ?>
		<div class="fes-fields">
		<?php
		if ( $this->characteristics['rich'] == 'yes' ) {
			$options = array( 'editor_height' => $this->characteristics['rows'], 'quicktags' => false, 'editor_class' => $req_class );
			if ( isset( $this->characteristics['insert_image'] ) && $this->characteristics['insert_image'] === 'yes' ) {
				$options['media_buttons'] = true;
			} else {
				$options['media_buttons'] = false;
			}
			printf( '<span class="fes-rich-validation" data-required="%s" data-type="rich" data-id="%s"></span>', $this->characteristics['required'], $this->name() );
			wp_editor( $value, $this->name(), $options );
		} elseif ( $this->characteristics['rich'] == 'teeny' ) {
			$options = array( 'editor_height' => $this->characteristics['rows'], 'quicktags' => false, 'teeny' => true, 'editor_class' => $req_class );
			if ( isset( $this->characteristics['insert_image'] ) && $this->characteristics['insert_image'] === 'yes' ) {
				$options['media_buttons'] = true;
			} else {
				$options['media_buttons'] = false;
			}
			printf( '<span class="fes-rich-validation" data-required="%s" data-type="rich" data-id="%s"></span>', $this->characteristics['required'], $this->name() );
			wp_editor( $value, $this->name(), $options );
		} else {  ?>
				<textarea class="textareafield<?php echo $this->required_class( $readonly ); ?>" id="<?php echo $this->name(); ?>" name="<?php echo $this->name(); ?>" data-required="<?php echo $required; ?>" data-type="textarea"<?php $this->required_html5( $readonly ); ?> placeholder="<?php echo esc_attr( $this->placeholder() ); ?>" rows="<?php echo esc_attr( $this->characteristics['rows'] ); ?>" cols="<?php echo esc_attr( $this->characteristics['cols'] ); ?>"><?php echo esc_textarea( $value ) ?></textarea>
			<?php } ?>
		</div>
		<?php
		$output .= ob_get_clean();
		$output .= '</fieldset>';
		return $output;
	}

	/** Returns the HTML to render a field for the formbuilder */
	public function render_formbuilder_field( $index = -2, $insert = false ) {
		$removable = $this->can_remove_from_formbuilder();
		$image_insert_name  = sprintf( '%s[%d][insert_image]', 'fes_input', $index );
		$image_insert_value = isset( $this->characteristics['insert_image'] ) ? $this->characteristics['insert_image'] : 'no';
		ob_start(); ?>
		<li class="post_content">
			<?php $this->legend( $this->title(), $this->get_label(), $removable ); ?>
			<?php FES_Formbuilder_Templates::hidden_field( "[$index][template]", $this->template() ); ?>

			<?php FES_Formbuilder_Templates::field_div( $index, $this->name(), $this->characteristics, $insert ); ?>
				<?php FES_Formbuilder_Templates::public_radio( $index, $this->characteristics, $this->form_name, true ); ?>
				<?php FES_Formbuilder_Templates::standard( $index, $this ); ?>
				<?php FES_Formbuilder_Templates::common_textarea( $index, $this->characteristics ); ?>

				<div class="fes-form-rows">
					<label><?php _e( 'Enable Image Insertion', 'edd_fes' ); ?></label>

					<div class="fes-form-sub-fields">
						<label>
							<input type="checkbox" name="<?php echo $image_insert_name ?>" value="yes"<?php checked( $image_insert_value, 'yes' ); ?> />
							<?php _e( 'Enable image upload in post area', 'edd_fes' ); ?>
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
			// no specific validation
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
			$values[ $name ] = wp_kses( $values[ $name ], fes_allowed_html_tags() );
		}
		return apply_filters( 'fes_sanitize_' . $this->template() . '_field', $values, $name, $save_id, $user_id );
	}
}
