<?php
class FES_Taxonomy_Field extends FES_Field {
	/** @var string Version of field */
	public $version = '1.0.0';

	/** @var bool For 3rd parameter of get_post/user_meta */
	public $single = false;

	/** @var array Supports are things that are the same for all fields of a field type. Like whether or not a field type supports jQuery Phoenix. Stored in obj, not db. */
	public $supports = array(
		'multiple'    => true,
		'is_meta'      => true,  // in object as public (bool) $meta;
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
			'can_change_meta_key'         => true,
			'can_add_to_formbuilder'      => true,
		),
		'template'    => 'taxonomy',
		'title'        => 'Taxonomy',
		'phoenix'    => true,
	);

	/** @var array Characteristics are things that can change from field to field of the same field type. Like the placeholder between two text fields. Stored in db. */
	public $characteristics = array(
		'name'         => '',
		'template'    => 'taxonomy',
		'public'       => true,
		'required'     => true,
		'label'        => '',
		'css'          => '',
		'default'      => '',
		'size'         => '',
		'help'         => '',
		'placeholder'  => '',
		'type'         => 'select',
		'order'        => 'ASC',
		'orderby'      => 'name',
		'exclude_type' => 'exclude',
		'exclude'      => '',
	);

	public function set_title() {
		$title = _x( 'Taxonomy', 'FES Field title translation', 'edd_fes' );
		$title = apply_filters( 'fes_' . $this->name() . '_field_title', $title );
		$this->supports['title'] = $title;
	}

	public function extending_constructor( ) {
		// exclude from submission form in admin
		add_filter( 'fes_templates_to_exclude_render_submission_form_admin', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'fes_templates_to_exclude_validate_submission_form_admin', array( $this, 'exclude_field' ), 10, 1  );
		add_filter( 'fes_templates_to_exclude_save_submission_form_admin', array( $this, 'exclude_field' ), 10, 1  );
	}

	public function exclude_field( $fields ) {
		array_push( $fields, $this->template() );
		return $fields;
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

		$taxonomy_objects = get_object_taxonomies( 'download' );
		$found = false;
		if ( !empty( $taxonomy_objects ) && is_array( $taxonomy_objects ) ) {
			foreach ( $taxonomy_objects as $id => $name ) {
				if ( $name === $this->name() ) {
					$found = true;
					break;
				}
			}
		} else {
			return;
		}

		if ( ! $found ){
			return;
		}

		$user_id      = apply_filters( 'fes_render_taxonomy_field_user_id_frontend', $user_id, $this->id );
		$readonly     = apply_filters( 'fes_render_taxonomy_field_readonly_frontend', $readonly, $user_id, $this->id );
		$exclude_type = $this->characteristics['exclude_type'];
		$exclude      = $this->characteristics['exclude'];
		$taxonomy     = $this->name();
		$required     = $this->required( $readonly );

		$terms   = array();
		$post_id = $this->save_id;
		if ( $post_id && $this->characteristics['type'] == 'text' ) {
			$terms = wp_get_post_terms( $post_id, $taxonomy, array( 'fields' => 'names' ) );
		} elseif ( $post_id ) {
			$terms = wp_get_post_terms( $post_id, $taxonomy, array( 'fields' => 'ids' ) );
		}
		$output        = '';
		$output     .= sprintf( '<fieldset class="fes-el %1s %2s %3s">', $this->template(), $this->name(), $this->css() );
		$output    .= $this->label( $readonly );
		ob_start(); ?>
		<div class="fes-fields">
			<?php
			switch ( $this->characteristics['type'] ) {
			case 'select':

				$selected = $terms ? $terms[0] : '';
				$required = sprintf( 'data-required="%s" data-type="select"', $required );

				$select = wp_dropdown_categories( array(
					'show_option_none' => __( '-- Select --', 'edd_fes' ),
					'hierarchical'     => 1,
					'hide_empty'       => 0,
					'orderby'          => $this->characteristics['orderby'],
					'order'            => $this->characteristics['order'],
					'name'             => $taxonomy . '[]',
					'id'               => $taxonomy,
					'taxonomy'         => $taxonomy,
					'echo'             => 0,
					'title_li'         => '',
					'class'            => $taxonomy,
					$exclude_type      => $exclude,
					'selected'         => $selected,
				) );
				echo str_replace( '<select', '<select ' . $required, $select );
				break;

			case 'multiselect':
				$selected_multiple = $terms ? $terms : array();
				$selected = is_array( $selected_multiple ) && !empty( $selected_multiple ) ? $selected_multiple[0] : '';
				$required = sprintf( 'data-required="%s" data-type="multiselect"', $required );
				$walker   = new FES_Walker_Category_Multi();

				$select = wp_dropdown_categories( array(
					'show_option_none'  => __( '-- Select --', 'edd_fes' ),
					'hierarchical'      => 1,
					'hide_empty'        => 0,
					'orderby'           => $this->characteristics['orderby'],
					'order'             => $this->characteristics['order'],
					'name'              => $taxonomy . '[]',
					'id'                => $taxonomy,
					'taxonomy'          => $taxonomy,
					'echo'              => 0,
					'title_li'          => '',
					'class'             => $taxonomy . ' multiselect',
					$exclude_type       => $exclude,
					'selected'          => $selected,
					'selected_multiple' => $selected_multiple,
					'walker'            => $walker
				) );

				echo str_replace( '<select', '<select multiple="multiple" ' . $required, $select );
				break;

			case 'checkbox':
				fes_category_checklist( $post_id, false, $this->characteristics, $taxonomy );
				break;
			case 'text':
			default: ?>
					<input class="textfield<?php echo $this->required_class( $readonly ); ?>" id="<?php echo $this->name(); ?>" type="text" data-required="<?php echo $required; ?>" data-type="text"<?php $this->required_html5( $readonly ); ?> name="<?php echo esc_attr( $this->name() ); ?>" value="<?php echo esc_attr( implode( ', ', $terms ) ); ?>" size="40" />
					<script type="text/javascript">
						jQuery(function(){
							jQuery('#<?php echo $this->name(); ?>').suggest( fes_form.ajaxurl + '?action=fes_ajax_taxonomy_search&tax=<?php echo $this->name(); ?>', { delay: 500, minchars: 2, multiple: true, multipleSep: ', ' } );
						});
					</script>

					<?php
				break;
			} ?>
		</div>

		<?php
		$output .= ob_get_clean();
		$output .= '</fieldset>';
		return $output;
	}


	public function display_field( $user_id = -2, $single = false ) {

		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}
		$user_id   = apply_filters( 'fes_display_' . $this->template() . '_field_user_id', $user_id, $this->id );

		if( ! taxonomy_exists( $this->name() ) ) {

			return __( 'Invalid taxonomy name. Please correct it from the form editor', 'edd_fes' );

		}

		ob_start(); ?>
			<?php if ( $single ) { ?>
			<table class="fes-display-field-table">
			<?php } ?>

			<tr class="fes-display-field-row <?php echo $this->template(); ?>" id="<?php echo $this->name(); ?>">
				<td class="fes-display-field-label"><?php echo $this->get_label(); ?></td>
				<td class="fes-display-field-values">
					<?php
					echo get_the_term_list( $this->save_id, $this->name(), '', ', ' );
					?>
				</td>
			</tr>

			<?php if ( $single ) { ?>
			</table>
			<?php } ?>
		<?php
		return ob_get_clean();
	}

	public function formatted_data( $user_id = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if( ! taxonomy_exists( $this->name() ) ) {

			return '';

		}

		$user_id   = apply_filters( 'fes_formatted_' . $this->template() . '_field_user_id', $user_id, $this->id );
		$value     = get_the_term_list( $this->save_id, $this->name(), '', ', ' );
		return $value;
	}

	/** Returns the HTML to render a field for the formbuilder */
	public function render_formbuilder_field( $index = -2, $insert = false ) {
		$removable    = $this->can_remove_from_formbuilder();
		$type_name          = sprintf( '%s[%d][type]'        , 'fes_input', $index );
		$order_name         = sprintf( '%s[%d][order]'       , 'fes_input', $index );
		$orderby_name       = sprintf( '%s[%d][orderby]'     , 'fes_input', $index );
		$exclude_type_name  = sprintf( '%s[%d][exclude_type]', 'fes_input', $index );
		$exclude_name       = sprintf( '%s[%d][exclude]'     , 'fes_input', $index );
		$type_value         = isset( $this->characteristics['type'] ) ? esc_attr( $this->characteristics['type'] ) : '';
		$order_value        = isset( $this->characteristics['order'] ) ? esc_attr( $this->characteristics['order'] ) : '';
		$orderby_value      = isset( $this->characteristics['orderby'] ) ? esc_attr( $this->characteristics['orderby'] ) : '';
		$exclude_type_value = isset( $this->characteristics['exclude_type'] ) ? esc_attr( $this->characteristics['exclude_type'] ) : '';
		$exclude_value      = isset( $this->characteristics['exclude'] ) ? esc_attr( $this->characteristics['exclude'] ) : '';
		ob_start(); ?>
		<li class="custom-field taxonomy">
			<?php $this->legend( $this->title(), $this->get_label(), $removable ); ?>
			<?php FES_Formbuilder_Templates::hidden_field( "[$index][template]", $this->template() ); ?>

			<?php FES_Formbuilder_Templates::field_div( $index, $this->name(), $this->characteristics, $insert, $insert ); ?>
			<?php FES_Formbuilder_Templates::public_radio( $index, $this->characteristics, $this->form_name );
			$field_name_value = $this->name();
			$custom_field     = $this->supports['position'] == 'custom';
			$values           = $this->characteristics;
			$required         = isset( $this->supports['permissions']['can_remove_from_formbuilder'] ) ? $this->supports['permissions']['can_remove_from_formbuilder'] : true;
			$force_required   = isset( $this->supports['permissions']['field_always_required'] ) ? $this->supports['permissions']['field_always_required'] : false;
			$template         = $this->characteristics['template'];
			$tpl              = '%s[%d][%s]';
			$required_name    = sprintf( $tpl, 'fes_input', $index, 'required' );
			$field_name       = sprintf( $tpl, 'fes_input', $index, 'name' );
			$label_name       = sprintf( $tpl, 'fes_input', $index, 'label' );
			$help_name        = sprintf( $tpl, 'fes_input', $index, 'help' );
			$css_name         = sprintf( $tpl, 'fes_input', $index, 'css' );

			if ( $force_required ) {
				$required = true;
			} else {
				// if saved field before
				if (  isset( $values['required'] ) ) {
					if (  $values['required'] === 'yes' ) {
						$required = true;
					} else {
						$required = false;
					}
				} else {
					// no change
				}
			}

			if ( $required ) {
				$required = 'yes';
			} else {
				$required = 'no';
			}

			$template           = !empty( $values['template'] ) ? $values['template'] : $template;
			$label_value        = isset( $values['label'] ) && ! empty( $values['label'] ) ? esc_attr( $values['label'] ) : esc_attr( ucwords( str_replace( '_', ' ', $template ) ) );
			$help_value         = isset( $values['help'] )? esc_textarea( $values['help'] ) : '';
			$css_value          = isset( $values['css'] )? esc_attr( $values['css'] ) : '';
			$meta_type          = "yes"; // for post meta on custom fields

			$exclude = array( 'email_to_use_for_contact_form', 'name_of_store', 'recaptcha' );
			if ( $custom_field && in_array( $field_name_value, $exclude ) ) {
				$custom_field = false;
			}

			$taxonomy_objects = get_object_taxonomies( 'download' );
			$count = 0;
			if ( !empty( $taxonomy_objects ) && is_array( $taxonomy_objects ) ) {
				foreach ( $taxonomy_objects as $id => $name ) {
					if ( $name === 'download_category' || $name === 'download_tag' ) {
						continue;
					}
					$count++;
				}
			} else {
				echo __( 'You have no custom taxonomies', 'edd_fes' );
				return;
			}

			if ( $count < 1 ){
				echo __( 'You have no custom taxonomies', 'edd_fes' );
				return;
			}

			do_action( 'fes_add_field_to_common_form_element', $tpl, 'fes_input', $index, $values ); ?>

			<div class="fes-form-rows required-field">
				<label><?php _e( 'Required', 'edd_fes' ); ?></label>
				<div class="fes-form-sub-fields">
					<input type="radio" name="<?php echo $required_name; ?>" value="yes" <?php checked( $required, 'yes' ); ?>> <?php _e( 'Yes', 'edd_fes' ); ?>
					<input type="radio" name="<?php echo $required_name; ?>" value="no" <?php checked( $required, 'no' ); ?>> <?php _e( 'No', 'edd_fes' ); ?>
				</div>
			</div>

			<div class="fes-form-rows">
				<label><?php _e( 'Field Label', 'edd_fes' ); ?></label>
				<input type="text" data-type="label" name="<?php echo $label_name; ?>" value="<?php echo $label_value; ?>" class="smallipopInput" title="<?php _e( 'Enter a title of this field', 'edd_fes' ); ?>">
			</div>

			<div class="fes-form-rows">
				<label><?php _e( 'Which Taxonomy?', 'edd_fes' ); ?></label>
				<div class="fes-form-sub-fields">
					<?php $taxonomy_objects = get_object_taxonomies( 'download' );
					$output = "<select style='width:200px;' name='$field_name' id='$field_name' class='chosen'>\n";
					foreach ( $taxonomy_objects as $id => $name ) {
						if ( $name === 'download_category' || $name === 'download_tag' ) {
							continue;
						}
						$select = selected( $name, $field_name_value, false );
						$output .= "\t<option value='$name' $select>$name</option>\n";
					}
					$output .= "</select>";

					// todo: Use Select2 when WordPress or EDD adds it to core
					$output .= '<script type="text/javascript">jQuery(function() {jQuery(".chosen").chosen({width: "200px"});});</script>';
					echo $output;
				?>
				</div>
			</div>


			<div class="fes-form-rows">
				<label><?php _e( 'Help text', 'edd_fes' ); ?></label>
				<textarea name="<?php echo $help_name; ?>" class="smallipopInput" title="<?php _e( 'Give the user some information about this field', 'edd_fes' ); ?>"><?php echo $help_value; ?></textarea>
			</div>

			<div class="fes-form-rows">
				<label><?php _e( 'CSS Class Name', 'edd_fes' ); ?></label>
				<input type="text" name="<?php echo $css_name; ?>" value="<?php echo $css_value; ?>" class="smallipopInput" title="<?php _e( 'Add a CSS class name for this field', 'edd_fes' ); ?>">
			</div>

			<div class="fes-form-rows">
				<label><?php _e( 'Type', 'edd_fes' ); ?></label>
				<select name="<?php echo $type_name ?>">
					<option value="select"<?php selected( $type_value, 'select' ); ?>><?php _e( 'Dropdown', 'edd_fes' ); ?></option>
					<option value="multiselect"<?php selected( $type_value, 'multiselect' ); ?>><?php _e( 'Multi Select', 'edd_fes' ); ?></option>
					<option value="checkbox"<?php selected( $type_value, 'checkbox' ); ?>><?php _e( 'Checkbox', 'edd_fes' ); ?></option>
					<option value="text"<?php selected( $type_value, 'text' ); ?>><?php _e( 'Text Input', 'edd_fes' ); ?></option>
				</select>
			</div>

			<div class="fes-form-rows">
				<label><?php _e( 'Order By', 'edd_fes' ); ?></label>
				<select name="<?php echo $orderby_name ?>">
					<option value="name"<?php selected( $orderby_value, 'name' ); ?>><?php _e( 'Name', 'edd_fes' ); ?></option>
					<option value="id"<?php selected( $orderby_value, 'id' ); ?>><?php _e( 'Term ID', 'edd_fes' ); ?></option>
					<option value="slug"<?php selected( $orderby_value, 'slug' ); ?>><?php _e( 'Slug', 'edd_fes' ); ?></option>
					<option value="count"<?php selected( $orderby_value, 'count' ); ?>><?php _e( 'Count', 'edd_fes' ); ?></option>
					<option value="term_group"<?php selected( $orderby_value, 'term_group' ); ?>><?php _e( 'Term Group', 'edd_fes' ); ?></option>
				</select>
			</div>

			<div class="fes-form-rows">
				<label><?php _e( 'Order', 'edd_fes' ); ?></label>
				<select name="<?php echo $order_name ?>">
					<option value="ASC"<?php selected( $order_value, 'ASC' ); ?>><?php _e( 'ASC', 'edd_fes' ); ?></option>
					<option value="DESC"<?php selected( $order_value, 'DESC' ); ?>><?php _e( 'DESC', 'edd_fes' ); ?></option>
				</select>
			</div>

			<div class="fes-form-rows">
				<label><?php _e( 'Selection Type', 'edd_fes' ); ?></label>
				<select name="<?php echo $exclude_type_name ?>">
					<option value="exclude"<?php selected( $exclude_type_value, 'exclude' ); ?>><?php _e( 'Exclude', 'edd_fes' ); ?></option>
					<option value="include"<?php selected( $exclude_type_value, 'include' ); ?>><?php _e( 'Include', 'edd_fes' ); ?></option>
					<option value="child_of"<?php selected( $exclude_type_value, 'child_of' ); ?>><?php _e( 'Child of', 'edd_fes' ); ?></option>
				</select>
			</div>

			<div class="fes-form-rows">
				<label><?php _e( 'Selection terms', 'edd_fes' ); ?></label>
				<input type="text" class="smallipopInput" name="<?php echo $exclude_name; ?>" title="<?php _e( 'Enter the term IDs as comma separated (without space) to exclude/include in the form.', 'edd_fes' ); ?>" value="<?php echo $exclude_value; ?>" />
			</div>

			</div>
		</li>
		<?php
		return ob_get_clean();
	}

	public function validate( $values = array(), $save_id = -2, $user_id = -2 ) {
		$name = $this->name();
		$return_value = false;
		switch ( $this->characteristics['type'] ) {
			case 'select':
				if ( !empty( $values[ $name ] ) && isset( $values[ $name ] ) && $values[ $name ] !== '-1' && $values[ $name ] !== -1 && $values[ $name ] !== ''  ) {
					// if the value is set
				} else {
					// if required but isn't present
					if ( $this->required() ) {
						$return_value = __( 'Please fill out this field.', 'edd_fes' );
					}
				}
				break;

			case 'multiselect':
				if ( ! empty( $values[ $name ] ) && isset( $values[ $name ][0] ) && $values[ $name ][0] !== '-1' && $values[ $name ][0] !== -1 && $values[ $name ][0] === '' ){
					unset( $values [ $name ][0] );
				}

				if ( ! empty( $values[ $name ] ) ) {
					// if the value is set
				} else {
					// if required but isn't present
					if ( $this->required() ) {
						$return_value = __( 'Please select at least 1 option', 'edd_fes' );
					}
				}
				break;

			case 'checkbox':
				if ( !empty( $values[ $name ] ) ) {
					// if the value is set

				} else {
					// if the checkbox is required but isn't present
					if ( $this->required() ) {
						if ( is_array( $this->characteristics['options'] ) ) {
							$return_value = __( 'Please select at least 1 option', 'edd_fes' );
						} else {
							$return_value = __( 'Please check the checkbox', 'edd_fes' );
						}
					}
				}
				break;
			case 'text':
			default:
				if ( !empty( $values[ $name ] ) ) {
					// no specific validation
				} else {
					// if required but isn't present
					if ( $this->required() ) {
						$return_value = __( 'Please fill out this field.', 'edd_fes' );
					}
				}
				break;
		}
		return apply_filters( 'fes_validate_' . $this->template() . '_field', $return_value, $values, $name, $save_id, $user_id );
	}

	public function sanitize( $values = array(), $save_id = -2, $user_id = -2 ) {
		$name = $this->name();

		switch ( $this->characteristics['type'] ) {
			case 'select':
				if ( !empty( $values[ $name ][0] ) ) {
					$values[ $name ] = trim( $values[ $name ][0] );
					$values[ $name ] = sanitize_text_field( $values[ $name ] );
				}
				break;

			case 'multiselect':
				if ( ! empty( $values[ $name ] ) ) {
					if ( is_array( $values[ $name ] ) ) {
						foreach ( $values[ $name ] as $key => $string ) {
							$values[ $name ][ $key ] = trim( $string );
							$values[ $name ][ $key ] = sanitize_text_field( $values[ $name ][ $key ] );
						}
					} else {
						$values[ $name ] = trim( $values[ $name ] );
						$values[ $name ] = sanitize_text_field( $values[ $name ] );
					}
				}
				break;

			case 'checkbox':
				if ( ! empty( $values[ $name ] ) ) {
					if ( is_array( $values[ $name ] ) ) {
						foreach ( $values[ $name ] as $key => $string ) {
							$values[ $name ][ $key ] = trim( $string );
							$values[ $name ][ $key ] = sanitize_text_field( $values[ $name ][ $key ] );
						}
					} else {
						$values[ $name ] = trim( $values[ $name ] );
						$values[ $name ] = sanitize_text_field( $values[ $name ] );
					}
				}
				break;
			case 'text':
			default:
				if ( !empty( $values[ $name ] ) ) {
					$values[ $name ] = trim( $values[ $name ] );
					$values[ $name ] = sanitize_text_field( $values[ $name ] );
				}
				break;
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

		if ( is_object_in_taxonomy( 'download', $this->name() ) ) {
			$tax = $value;
			// if it's not an array, make it one
			if ( !is_array( $tax ) ) {
				$tax = array(
					$tax
				);
			}
			if ( $this->characteristics[ 'type' ] == 'text' ) {
				$hierarchical = array_map( 'trim', array_map( 'strip_tags', explode( ',', $value ) ) );
				$value = wp_set_object_terms( $save_id, $hierarchical, $this->name() );
			} else {
				if ( is_taxonomy_hierarchical( $this->name() ) ) {
					$value = wp_set_post_terms( $save_id, $value, $this->name() );
				} else {
					if ( $tax ) {
						$non_hierarchical = array();
						foreach ( $tax as $value ) {
							$term = get_term_by( 'id', $value, $this->name() );
							if ( $term && !is_wp_error( $term ) ) {
								$non_hierarchical[] = $term->name;
							}
						}
						$value = wp_set_post_terms( $save_id, $non_hierarchical, $this->name() );
					}
				} // hierarchical
			} // is text
		} // is object tax

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

		if ( is_object_in_taxonomy( 'download', $this->name() ) ) {
			$tax = $value;
			// if it's not an array, make it one
			if ( !is_array( $tax ) ) {
				$tax = array(
					$tax
				);
			}
			if ( $this->characteristics[ 'type' ] == 'text' ) {
				$hierarchical = array_map( 'trim', array_map( 'strip_tags', explode( ',', $value ) ) );
				$value = wp_set_object_terms( $save_id, $hierarchical, $this->name() );
			} else {
				if ( is_taxonomy_hierarchical( $this->name() ) ) {
					$value = wp_set_post_terms( $save_id, $value, $this->name() );
				} else {
					if ( $tax ) {
						$non_hierarchical = array();
						foreach ( $tax as $value ) {
							$term = get_term_by( 'id', $value, $this->name() );
							if ( $term && !is_wp_error( $term ) ) {
								$non_hierarchical[] = $term->name;
							}
						}
						$value = wp_set_post_terms( $save_id, $non_hierarchical, $this->name() );
					}
				} // hierarchical
			} // is text
		} // is object tax

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

		$exclude_type = $this->characteristics['exclude_type'];
		$exclude      = $this->characteristics['exclude'];
		$taxonomy     = $this->name();
		$value     = '';

		if ( $save_id && $this->characteristics['type'] == 'text' ) {
			$value = wp_get_post_terms( $save_id, $taxonomy, array( 'fields' => 'names' ) );
		} elseif ( $save_id ) {
			$value = wp_get_post_terms( $save_id, $taxonomy, array( 'fields' => 'ids' ) );
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

		$exclude_type = $this->characteristics['exclude_type'];
		$exclude      = $this->characteristics['exclude'];
		$taxonomy     = $this->name();
		$value     = '';

		if ( $save_id && $this->characteristics['type'] == 'text' ) {
			$value = wp_get_post_terms( $save_id, $taxonomy, array( 'fields' => 'names' ) );
		} elseif ( $save_id ) {
			$value = wp_get_post_terms( $save_id, $taxonomy, array( 'fields' => 'ids' ) );
		}

		$value = apply_filters( 'fes_get_field_value_return_value_frontend', $value, $save_id, $user_id, $public  );
		return $value;
	}

}
