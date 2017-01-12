<?php
class FES_File_Upload_Field extends FES_Field {
	/** @var string Version of field */
	public $version = '1.0.0';	
	
	/** @var bool For 3rd parameter of get_post/user_meta */
	public $single = true;

	/** @var array Supports are things that are the same for all fields of a field type. Like whether or not a field type supports jQuery Phoenix. Stored in obj, not db. */
	public $supports = array(
		'multiple'    => true,
		'is_meta'     => true,  // in object as public (bool) $meta;
		'forms'       => array(
			'registration'     => false,
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
		'template'    => 'file_upload',
		'title'       => 'File Upload',
		'phoenix'    => false,
	);

	/** @var array Characteristics are things that can change from field to field of the same field type. Like the placeholder between two file_upload fields. Stored in db. */
	public $characteristics = array(
		'name'        => '',
		'template'    => 'file_upload',
		'public'      => true,
		'required'    => false,
		'label'       => '',
		'css'         => '',
		'default'     => '',
		'size'        => '',
		'help'        => '',
		'placeholder' => '',
		'count'       => '1',
		'single'      => false,
	);


	public function set_title() {
		$title = _x( 'File Upload', 'FES Field title translation', 'edd_fes' );
		$title = apply_filters( 'fes_' . $this->name() . '_field_title', $title );
		$this->supports['title'] = $title;		
	}

	/** Returns the HTML to render a field in admin */
	public function render_field_admin( $user_id = -2, $readonly = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $readonly === -2 ) {
			$readonly = $this->readonly;
		}

		$user_id   = apply_filters( 'fes_render_file_upload_field_user_id_admin', $user_id, $this->id );
		$readonly  = apply_filters( 'fes_render_file_upload_field_readonly_admin', $readonly, $user_id, $this->id );
		$value     = $this->get_field_value_admin( $this->save_id, $user_id, $readonly );

		$single = false;
		if ( $this->type == 'submission' ) {
			$single = true;
		}

		$uploaded_items = $value;
		if ( ! is_array( $uploaded_items ) || empty( $uploaded_items ) ) {
			$uploaded_items = array( 0 => '' );
		}

		$max_files = 0;
		if ( $this->characteristics['count'] > 0 ) {
			$max_files = $this->characteristics['count'];
		}

		$output        = '';
		$output     .= sprintf( '<fieldset class="fes-el %1s %2s %3s">', $this->template(), $this->name(), $this->css() );
		$output    .= $this->label( $readonly );
		ob_start(); ?>
			<div class="fes-fields">
				 <table class="<?php echo sanitize_key( $this->name() ); ?>">
					<thead>
						<tr>
							<td class="fes-file-column" colspan="2"><?php _e( 'File URL', 'edd_fes' ); ?></td>
							<?php if ( fes_is_admin() ) { ?>
							<td class="fes-download-file">
									 <?php _e( 'Download File', 'edd_fes' ); ?>
							</td>
							<?php } ?>
							<?php if ( empty( $this->characteristics['single'] ) || $this->characteristics['single'] !== 'yes' ) { ?>
									 <td class="fes-remove-column">&nbsp;</td>
							<?php } ?>
						 </tr>
					</thead>
					<tbody class="fes-variations-list-<?php echo sanitize_key( $this->name() ); ?>">
							 <input type="hidden" id="fes-upload-max-files-<?php echo sanitize_key( $this->name() ); ?>" value="<?php echo $max_files; ?>" />
							<?php
							foreach ( $uploaded_items as $index => $attach_id ) {
								$download = wp_get_attachment_url( $attach_id ); ?>
								<tr class="fes-single-variation">
									 <td class="fes-url-row">
												<input type="text" class="fes-file-value" placeholder="<?php _e( "http://", 'edd_fes' ); ?>" name="<?php echo $this->name(); ?>[<?php echo esc_attr( $index ); ?>]" value="<?php echo esc_attr( $download ); ?>" />
									 </td>
									 <td class="fes-url-choose-row" width="1%">
												<a href="#" class="edd-submit button upload_file_button" data-choose="<?php _e( 'Choose file', 'edd_fes' ); ?>" data-update="<?php _e( 'Insert file URL', 'edd_fes' ); ?>">
												<?php echo str_replace( ' ', '&nbsp;', __( 'Choose file', 'edd_fes' ) ); ?></a>
									 </td>
									 <?php if ( fes_is_admin()  ) { ?>
									 <td class="fes-download-file">
												<?php printf( '<a href="%s">%s</a>', wp_get_attachment_url( $attach_id ), __( 'Download File', 'edd_fes' ) ); ?>
									 </td>
									 <?php } ?>
									 <?php if ( empty( $this->characteristics['single'] ) || $this->characteristics['single'] !== 'yes' ) { ?>
									 <td width="1%" class="fes-delete-row">
												<a href="#" class="edd-fes-delete delete">
												<?php _e( '&times;', 'edd_fes' ); ?></a>
									 </td>
									<?php } ?>
								</tr>
							<?php } ?>
							<tr class="add_new" style="display:none !important;" id="<?php echo sanitize_key( $this->name() ); ?>"></tr>
					</tbody>
					<?php if ( empty( $this->characteristics['count'] ) || $this->characteristics['count'] > 1 ) : ?>
					<tfoot>
						<tr>
							<th colspan="5">
								<a href="#" class="edd-submit button insert-file-row" id="<?php echo sanitize_key( $this->name() ); ?>"><?php _e( 'Add File', 'edd_fes' ); ?></a>
							</th>
						</tr>
					</tfoot>
					<?php endif; ?>
				</table>
			</div> <!-- .fes-fields -->
		<?php
		$output .= ob_get_clean();
		$output .= '</fieldset>';
		return $output;
	}

	/** Returns the HTML to render a field in frontend */
	public function render_field_frontend( $user_id = -2, $readonly = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $readonly === -2 ) {
			$readonly = $this->readonly;
		}

		$user_id   = apply_filters( 'fes_render_file_upload_field_user_id_frontend', $user_id, $this->id );
		$readonly  = apply_filters( 'fes_render_file_upload_field_readonly_frontend', $readonly, $user_id, $this->id );
		$value     = $this->get_field_value_frontend( $this->save_id, $user_id, $readonly );
		$required  = $this->required( $readonly );
		$single = false;
		if ( $this->type == 'submission' ) {
			$single = true;
		}

		$uploaded_items = $value;
		if ( ! is_array( $uploaded_items ) || empty( $uploaded_items ) ) {
			$uploaded_items = array( 0 => '' );
		}

		$max_files = 0;
		if ( $this->characteristics['count'] > 0 ) {
			$max_files = $this->characteristics['count'];
		}
		$output        = '';
		$output     .= sprintf( '<fieldset class="fes-el %1s %2s %3s">', $this->template(), $this->name(), $this->css() );
		$output    .= $this->label( $readonly );
		ob_start(); ?>
			<div class="fes-fields">
				 <table class="<?php echo sanitize_key( $this->name() ); ?>">
					<thead>
						<tr>
							<th class="fes-file-column" colspan="2"><?php _e( 'File URL', 'edd_fes' ); ?></th>
							<?php if ( fes_is_admin() ) { ?>
							<th class="fes-download-file">
									 <?php _e( 'Download File', 'edd_fes' ); ?>
							</th>
							<?php } ?>
							<?php if ( empty( $this->characteristics['single'] ) || $this->characteristics['single'] !== 'yes' ) { ?>
									 <th class="fes-remove-column">&nbsp;</th>
							<?php } ?>
						 </tr>
					</thead>
					<tbody class="fes-variations-list-<?php echo sanitize_key( $this->name() ); ?>">
							 <input type="hidden" id="fes-upload-max-files-<?php echo sanitize_key( $this->name() ); ?>" value="<?php echo $max_files; ?>" />
							<?php
							foreach ( $uploaded_items as $index => $attach_id ) {
								$download = wp_get_attachment_url( $attach_id ); ?>
								<tr class="fes-single-variation">
									 <td class="fes-url-row">
												<input type="text" class="fes-file-value" placeholder="<?php _e( "http://", 'edd_fes' ); ?>" name="<?php echo $this->name(); ?>[<?php echo esc_attr( $index ); ?>]" value="<?php echo esc_attr( $download ); ?>" />
									 </td>
									 <td class="fes-url-choose-row">
												<a href="#" class="edd-submit button upload_file_button" data-choose="<?php _e( 'Choose file', 'edd_fes' ); ?>" data-update="<?php _e( 'Insert file URL', 'edd_fes' ); ?>">
												<?php echo str_replace( ' ', '&nbsp;', __( 'Choose file', 'edd_fes' ) ); ?></a>
									 </td>
									 <?php if ( empty( $this->characteristics['single'] ) || $this->characteristics['single'] !== 'yes' ) { ?>
									 <td width="1%" class="fes-delete-row">
												<a href="#" class="edd-fes-delete delete">
												<?php _e( '&times;', 'edd_fes' ); ?></a>
									 </td>
									<?php } ?>
								</tr>
							<?php } ?>
							<tr class="add_new" style="display:none !important;" id="<?php echo sanitize_key( $this->name() ); ?>"></tr>
					</tbody>
					<?php if ( empty( $this->characteristics['count'] ) || $this->characteristics['count'] > 1 ) : ?>
					<tfoot>
						<tr>
							<th colspan="5">
								<a href="#" class="edd-submit button insert-file-row" id="<?php echo sanitize_key( $this->name() ); ?>"><?php _e( 'Add File', 'edd_fes' ); ?></a>
							</th>
						</tr>
					</tfoot>
					<?php endif; ?>
				</table>
			</div> <!-- .fes-fields -->
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
		$value     = $this->get_field_value_frontend( $this->save_id, $user_id );
		ob_start(); ?>

			<?php if ( $single ) { ?>
			<table class="fes-display-field-table">
			<?php } ?>

				<tr class="fes-display-field-row <?php echo $this->template(); ?>" id="<?php echo $this->name(); ?>">
					<td class="fes-display-field-label"><?php echo $this->get_label(); ?></td>
					<td class="fes-display-field-values">
						<?php
						$uploads = array();
						if ( is_array( $value ) ) {
							foreach ( $value as $attachment_id ) {
								$uploads[] = wp_get_attachment_link( $attachment_id, 'thumbnail', false, true );
							}
							$value = implode( '<br />', $uploads );
						}
						echo $value;
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

		$user_id   = apply_filters( 'fes_formatted_' . $this->template() . '_field_user_id', $user_id, $this->id );
		$value     = $this->get_field_value_frontend( $this->save_id, $user_id );
		$uploads = array();
		if ( is_array( $value ) ) {
			foreach ( $value as $attachment_id ) {
				$uploads[] = wp_get_attachment_link( $attachment_id, 'thumbnail', false, true );
			}
			$value = implode( '<br />', $uploads );
		}
		return $value;
	}

	/** Returns the HTML to render a field for the formbuilder */
	public function render_formbuilder_field( $index = -2, $insert = false ) {
		$removable    = $this->can_remove_from_formbuilder();
		$max_files_name  = sprintf( '%s[%d][count]', 'fes_input', $index );
		$max_files_value = $this->characteristics['count'];
		$count           = esc_attr( __( 'Number of files which can be uploaded', 'edd_fes' ) );
		ob_start(); ?>
				<li class="custom-field custom_image">
						<?php $this->legend( $this->title(), $this->get_label(), $removable ); ?>
						<?php FES_Formbuilder_Templates::hidden_field( "[$index][template]", $this->template() ); ?>

						<?php FES_Formbuilder_Templates::field_div( $index, $this->name(), $this->characteristics, $insert ); ?>
								<?php FES_Formbuilder_Templates::public_radio( $index, $this->characteristics, $this->form_name ); ?>
								<?php FES_Formbuilder_Templates::standard( $index, $this ); ?>

								<div class="fes-form-rows">
										<label><?php _e( 'Max. files', 'edd_fes' ); ?></label>
										<input type="text" class="smallipopInput" name="<?php echo esc_html( $max_files_name ); ?>" value="<?php echo esc_html( $max_files_value ); ?>" title="<?php echo esc_html( $count ); ?>">
								</div>
						</div>
				</li>
				<?php
		return ob_get_clean();
	}

	public function validate( $values = array(), $save_id = -2, $user_id = -2 ) {
		$name = $this->name();
		$return_value = false;
		if ( $this->required() ) {
			if ( !empty( $values[ $name ] ) ) {
				if ( is_array( $values[ $name ] ) ){
					foreach( $values[ $name ] as $key => $file  ){
						if ( filter_var( $file, FILTER_VALIDATE_URL ) === false ) {
							// if that's not a url
							$return_value = __( 'Please enter a valid URL', 'edd_fes' );
							break;
						}
					}
				} else {
					$return_value = __( 'Please fill out this field.', 'edd_fes' );
				}
			} else {
				$return_value = __( 'Please fill out this field.', 'edd_fes' );
			}
		}		
		return apply_filters( 'fes_validate_' . $this->template() . '_field', $return_value, $values, $name, $save_id, $user_id );
	}

	public function sanitize( $values = array(), $save_id = -2, $user_id = -2 ) {
		$name = $this->name();
		if ( ! empty( $values[ $name ] ) ) {
			if ( is_array( $values[ $name ] ) ){
				foreach( $values[ $name ] as $key => $option  ){
					$values[ $name ][ $key ] = filter_var( trim( $values[ $name ][ $key ] ), FILTER_SANITIZE_URL );
				}
			}
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
		if ( !is_array( $value ) ) {
			return;
		}

		if ( $this->type === 'user' ) {
			delete_user_meta( $save_id, $this->name() );
			$ids = array();
			foreach ( $value as $file => $url ) {
				if ( empty ( $url ) ) {
					continue;
				}
				$attachment_id = fes_get_attachment_id_from_url( $url );
				$ids[] = $attachment_id;
			}
			update_user_meta( $save_id, $this->name(), $ids );
		} else if ( $this->type === 'post' ) {
			$ids = array();
			// We need to detach all previously attached files for this field. See #559
			$old_files = get_post_meta( $save_id, $this->name(), true );
			if ( ! empty( $old_files ) && is_array( $old_files ) ) {
				foreach ( $old_files as $file_id ) {
					global $wpdb;
					$wpdb->update(
						$wpdb->posts,
						array(
							'post_parent' => 0,
						),
						array(
							'ID' => $file_id,
						),
						array(
							'%d'
						),
						array(
							'%d'
						)
					);
				}
			}
			foreach ( $value as $file => $url ) {
				if ( empty ( $url ) ) {
					continue;
				}
				if ( ! EDD_FES()->vendors->user_is_admin() ) {
					$author_id = get_post_field( 'post_author', $save_id );
				} else {
					$author_id = 0;
				}
				$attachment_id = fes_get_attachment_id_from_url( $url, $author_id );
				fes_associate_attachment( $attachment_id, $save_id );
				$ids[] = $attachment_id;
			}
			update_post_meta( $save_id, $this->name(), $ids );
		} else {
			// todo: do action
		}

		$this->value = $value;
		do_action( 'fes_save_field_after_save_admin', $save_id, $value, $user_id );
	}

	public function save_field_frontend( $save_id = -2, $value = array(), $user_id = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $save_id == -2 ) {
			$save_id = $this->save_id;
		}

		$user_id  = apply_filters( 'fes_save_field_user_id_frontend', $user_id, $save_id, $value );
		$value    = apply_filters( 'fes_save_field_value_frontend', $value, $save_id, $user_id );

		do_action( 'fes_save_field_before_save_frontend', $save_id, $value, $user_id );
		if ( !is_array( $value ) ) {
			return;
		}

		if ( $this->type === 'user' ) {
			delete_user_meta( $save_id, $this->name() );
			$ids = array();
			foreach ( $value as $file => $url ) {
				if ( empty ( $url ) ) {
					continue;
				}
				$attachment_id = fes_get_attachment_id_from_url( $url );
				$ids[] = $attachment_id;
			}
			update_user_meta( $save_id, $this->name(), $ids );
		} else if ( $this->type === 'post' ) {
			$ids = array();
			// We need to detach all previously attached files for this field. See #559
			$old_files = get_post_meta( $save_id, $this->name(), true );
			if ( ! empty( $old_files ) && is_array( $old_files ) ) {
				foreach ( $old_files as $file_id ) {
					global $wpdb;
					$wpdb->update(
						$wpdb->posts,
						array(
							'post_parent' => 0,
						),
						array(
							'ID' => $file_id,
						),
						array(
							'%d'
						),
						array(
							'%d'
						)
					);
				}
			}
			foreach ( $value as $file => $url ) {
				if ( empty ( $url ) ) {
					continue;
				}
				if ( ! EDD_FES()->vendors->user_is_admin() ) {
					$author_id = get_post_field( 'post_author', $save_id );
				} else {
					$author_id = 0;
				}
				$attachment_id = fes_get_attachment_id_from_url( $url, $author_id );
				fes_associate_attachment( $attachment_id, $save_id );

				$ids[] = $attachment_id;
			}
			update_post_meta( $save_id, $this->name(), $ids );
		} else {
			// todo: do action
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

		$value = '';
		if ( $this->type === 'user' ) {
			$value = get_user_meta( $save_id, $this->name(), $this->single );
		} else if ( $this->type === 'post' ) {
			$value = get_post_meta( $save_id, $this->name(), $this->single );
		} else {
			$value = apply_filters( 'fes_get_custom_file_upload_value_admin', $save_id, $user_id, $public );
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

		$value = '';
		if ( $this->type === 'user' ) {
			$value = get_user_meta( $save_id, $this->name(), $this->single );
		} else if ( $this->type === 'post' ) {
			$value = get_post_meta( $save_id, $this->name(), $this->single );
		} else {
			$value = apply_filters( 'fes_get_custom_file_upload_value_frontend', $save_id, $user_id, $public );
		}

		$value = apply_filters( 'fes_get_field_value_return_value_frontend', $value, $save_id, $user_id, $public  );
		return $value;
	}
}
