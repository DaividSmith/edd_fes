<?php
class FES_Multiple_Pricing_Field extends FES_Field {
	/** @var string Version of field */
	public $version = '1.0.0';	
	
	/** @var bool For 3rd parameter of get_post/user_meta */
	public $single = false;

	/** @var array Supports are things that are the same for all fields of a field type. Like whether or not a field type supports jQuery Phoenix. Stored in obj, not db. */
	public $supports = array(
		'multiple'    => false,
		'is_meta'     => true,  // in object as public (bool) $meta;
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
		'template' => 'multiple_pricing',
		'title'    => 'Prices and Files',
		'phoenix'  => false,
	);

	/** @var array Characteristics are things that can change from field to field of the same field type. Like the placeholder between two text fields. Stored in db. */
	public $characteristics = array(
		'name'        => 'multiple_pricing',
		'template'    => 'multiple_pricing',
		'public'      => false,
		'required'    => true,
		'label'       => '',
		'css'         => '',
		'default'     => '',
		'size'        => '',
		'help'        => '',
		'placeholder' => '',
		'names'       => 'yes',
		'prices'      => 'yes',
		'files'       => 'yes',
		'multiple'    => false,
		'columns'     => '',
		'single'      => 'no',
	);


	public function set_title() {
		$title = _x( 'Prices and Files', 'FES Field title translation', 'edd_fes' );
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
		array_push( $fields, 'multiple_pricing' );
		return $fields;
	}

	public function render_field_admin( $user_id = -2, $readonly = -2 ) {
		return ''; // don't render in the backend
	}

	/** Returns the HTML to render a field in frontend */
	public function render_field_frontend( $user_id = -2, $readonly = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $readonly === -2 ) {
			$readonly = $this->readonly;
		}

		$user_id   = apply_filters( 'fes_render_multiple_pricing_user_id_frontend', $user_id, $this->id );
		$readonly  = apply_filters( 'fes_render_multiple_pricing_readonly_frontend', $readonly, $user_id, $this->id );

		// this system of letters should just be replaced with booleans. It would make this whole thing way simpler.
		$names_disabled     = isset( $this->characteristics['names'] )    &&  $this->characteristics['names']    !== 'no'    ? false : true;
		$prices_disabled    = isset( $this->characteristics['prices'] )   &&  $this->characteristics['prices']   !== 'no'    ? false : true;
		$files_disabled     = isset( $this->characteristics['files'] )    &&  $this->characteristics['files']    !== 'no'    ? false : true;
		$predefined_on      = isset( $this->characteristics['multiple'] ) &&  $this->characteristics['multiple'] !== 'false' ? true  : false;
		$predefined_options = !empty( $this->characteristics['files'] )   &&  $this->characteristics['files']    ? esc_attr( $this->characteristics['files'] ) : false;
		$required = $this->required( $readonly );
		$post_id  = $this->get_save_id();
		if ( $post_id && $post_id > 0 ) {
			$files       = get_post_meta( $post_id, 'edd_download_files', true );
			$prices      = get_post_meta( $post_id, 'edd_variable_prices', true );
			$is_variable = (bool) get_post_meta( $post_id, '_variable_pricing', true );
			$combos      = array();
			$columns     = array();
			if ( $is_variable ) {
				$counter = 0;
				foreach ( $prices as $key => $option ) {
					$columns['file']        = ( isset( $files[$counter] ) && isset( $files[$counter]['file'] )? $files[$counter]['file'] : '' );
					$columns['price']       = ( isset( $option['amount'] )? $option['amount'] : '' );
					$columns['description'] = ( isset( $option['name'] )? $option['name'] : '' );
					$combos[$key]           = apply_filters( 'fes_multiple_pricing_variable_combo', $columns, $option );
					$counter++;
				}
			} else {
				$columns['file']        = ( isset( $files[0]['file'] )? $files[0]['file'] : '' );
				$columns['price']       = get_post_meta( $post_id, 'edd_price', true );
				$columns['description'] = ( isset( $prices[0]['name'] )? $prices[0]['name'] : '' );
				$columns 				= apply_filters( 'fes_multiple_pricing_single_combo', $columns );
				$combos                 = array( 0 => $columns );
			}
		} else {

			if ( $predefined_on && $this->characteristics['columns'] > 0 ) {
				$keys = count( $this->characteristics['columns'] );
				$new_values = array();
				$key = 0;
				foreach ( $this->characteristics['columns'] as $old_key => $value ) {
					if ( $old_key === 0 || $old_key % 2 == 0 ) {
						$new_values[$key]['description'] = $value['name'];
						$new_values[$key]['files'] = '';
					}
					else {
						$new_values[$key]['price'] = $value['price'];
						$key++;
					}
					unset( $this->characteristics['columns'][ $old_key ] );
				}
				$combos = $new_values;

			} else {
				$combos = array( 0 => array( 'description' => '', 'price' => '', 'files' => '' ) );
			}
		}
		$files      = $combos;
		$output        = '';
		$output     .= sprintf( '<fieldset class="fes-el %1s %2s %3s">', $this->template(), $this->name(), $this->css() );
		$output     .= $this->label( $readonly );
		ob_start(); ?>
		<div class="fes-fields">
			<table class="multiple">
				<thead>
					<tr>
						<?php if ( $this->characteristics['single'] !== 'yes' && ( !$names_disabled || $predefined_on )  ) { ?>
							<th class="fes-name-column"><?php _e( 'Name of Price Option', 'edd_fes' ); ?></th>
						<?php } ?>
						<?php if ( !$prices_disabled || $predefined_on ) { ?>
							<th class="fes-price-column"><?php printf( __( 'Amount (%s)', 'edd_fes' ), edd_currency_filter( '' ) ); ?></th>
						<?php } ?>
						<?php if ( !$files_disabled ) { ?>
							<th class="fes-file-column" colspan="2"><?php _e( 'File URL', 'edd_fes' ); ?></th>
						<?php } ?>
						<?php do_action( "fes_add_multiple_pricing_column" ); ?>
						<?php if ( ! ( $this->characteristics['single'] === 'yes' || $predefined_on ) ) { ?>
							<th class="fes-remove-column">&nbsp;</th>
						<?php } ?>
					</tr>
				</thead>
				<tbody  class="fes-variations-list-multiple">
				<?php
				foreach ( $files as $index => $file ) {
					if ( ! is_array( $file ) ) {
						$file = array(
							'file'        => '',
							'description' => '',
							'price'       => ''
						);
						$file = apply_filters( 'fes_default_new_multiple_price_row_values', $file );
					}
					$description = isset( $file['description'] ) ? $file['description']  : '';
					$price       = isset( $file['price'] ) ? $file['price']  : '';
					$file        = isset( $file['file'] ) ? $file['file']  : '';
					$required = $required ? 'data-required="yes" data-type="multiple"' : '' ?>
					<tr class="fes-single-variation">
						<?php if ( $this->characteristics['single'] !== 'yes' && ( !$names_disabled || $predefined_on ) ) { ?>
						<td class="fes-name-row">
							<?php if ( $names_disabled ) : ?>
								<span class="fes-name-value"><?php echo $description; ?></span>
								<input type="hidden" class="fes-name-value" name="option[<?php echo esc_attr( $index ); ?>][description]" id="options[<?php echo esc_attr( $index ); ?>][description]" rows="3" value="<?php echo esc_attr( $description ); ?>" <?php echo $required; ?>/>
							<?php else : ?>
								<input type="text" class="fes-name-value" name="option[<?php echo esc_attr( $index ); ?>][description]" id="options[<?php echo esc_attr( $index ); ?>][description]" rows="3" value="<?php echo esc_attr( $description ); ?>" <?php echo $required; ?>/>
							<?php endif; ?>
						</td>
						<?php } else { ?>
						<input type="hidden" class="fes-name-value" name="option[<?php echo esc_attr( $index ); ?>][description]" id="options[<?php echo esc_attr( $index ); ?>][description]" rows="3" value="<?php echo esc_attr( $description ); ?>" <?php echo $required; ?>/>
						<?php }
						if ( !$prices_disabled || $predefined_on ) { ?>
						<td class="fes-price-row">
							<?php if ( $prices_disabled ) : ?>
								<span class="fes-price-value"><?php echo edd_format_amount( esc_attr( $price ) ); ?></span>
								<input type="hidden" class="fes-price-value" name="option[<?php echo esc_attr( $index ); ?>][price]" id="options[<?php echo esc_attr( $index ); ?>][price]" value="<?php echo edd_format_amount( esc_attr( $price ) ); ?>" <?php echo $required; ?>/>
							<?php else : ?>
								<input type="text" class="fes-price-value" name="option[<?php echo esc_attr( $index ); ?>][price]" id="options[<?php echo esc_attr( $index ); ?>][price]" value="<?php echo edd_format_amount( esc_attr( $price ) ); ?>" <?php echo $required; ?>/>
							<?php endif; ?>
						</td>
						<?php }
						if ( !$files_disabled ) { ?>
						<td class="fes-url-row">
							<input type="text" class="fes-file-value" placeholder="<?php _e( "http://", 'edd_fes' ); ?>" name="files[<?php echo esc_attr( $index ); ?>]" value="<?php echo esc_attr( $file ); ?>" <?php echo $required; ?>/>
						</td>
						<td class="fes-url-choose-row">
							<a href="#" class="edd-submit button upload_file_button" data-choose="<?php _e( 'Choose file', 'edd_fes' ); ?>" data-update="<?php _e( 'Insert file URL', 'edd_fes' ); ?>">
							<?php echo str_replace( ' ', '&nbsp;', __( 'Upload', 'edd_fes' ) ); ?></a>
						</td>
						<?php }
						do_action( "fes_add_multiple_pricing_row_value", $file, $index ); ?>
						<?php if ( ! ( $this->characteristics['single'] === 'yes' || $predefined_on ) ) { ?>
						<td class="fes-delete-row">
							<a href="#" class="edd-fes-delete">
							<?php _e( '&times;', 'edd_fes' ); ?></a>
						</td>
						<?php } ?>
					</tr>
					<?php } ?>
					<tr class="add_new" style="display:none !important;" id="multiple"></tr>
				</tbody>
				<tfoot>
					<tr>
						<th colspan="5">
							<?php if ( ! ( $this->characteristics['single'] === 'yes' || $predefined_on ) ) { ?>
							<a href="#" class="edd-submit button insert-file-row" id="multiple"><?php _e( 'Add File', 'edd_fes' ); ?></a>
							<?php } ?>
						</th>
					</tr>
				</tfoot>
			</table>
		</div>
		<?php
		$output .= ob_get_clean();
		$output .= '</fieldset>';
		return $output;
	}

	/** Returns the HTML to render a field for the formbuilder */
	public function render_formbuilder_field( $index = -2, $insert = false ) {
		$removable          = $this->can_remove_from_formbuilder();
		$enable_column_name = sprintf( '%s[%d][multiple]', 'fes_input', $index );
		$column_names       = sprintf( '%s[%d][columns]', 'fes_input', $index );
		$has_column         = ( $this->characteristics && isset( $this->characteristics['multiple'] ) && $this->characteristics['multiple'] ) ? true : false;
		$this->characteristics['has_column'] = $has_column;
		ob_start(); ?>
		<li class="multiple_pricing">
			<?php $this->legend( $this->title(), $this->get_label(), $removable ); ?>
			<?php FES_Formbuilder_Templates::public_radio( $index, $this->characteristics, $this->form_name, true ); ?>
			<?php FES_Formbuilder_Templates::hidden_field( "[$index][template]", $this->template() ); ?>

			<?php FES_Formbuilder_Templates::field_div( $index, $this->name(), $this->characteristics, $insert ); ?>
				<?php FES_Formbuilder_Templates::standard( $index, $this );
				$tpl         = '%s[%d][%s]';
				$single_name = sprintf( $tpl, 'fes_input', $index, 'single' );
				$names_name  = sprintf( $tpl, 'fes_input', $index, 'names' );
				$prices_name = sprintf( $tpl, 'fes_input', $index, 'prices' );
				$files_name  = sprintf( $tpl, 'fes_input', $index, 'files' );
				$single      = esc_attr( $this->characteristics['single'] );
				$names       = esc_attr( $this->characteristics['names'] );
				$prices      = esc_attr( $this->characteristics['prices'] );
				$files       = esc_attr( $this->characteristics['files'] );	?>

				<div class="fes-form-rows required-field">
					<label><?php _e( 'Single Price/Upload', 'edd_fes' ); ?></label>
					<div class="fes-form-sub-fields">
						<label><input type="radio" name="<?php echo $single_name; ?>" value="yes"<?php checked( $single, 'yes' ); ?>> <?php _e( 'Yes', 'edd_fes' ); ?> </label>
						<label><input type="radio" name="<?php echo $single_name; ?>" value="no"<?php checked( $single, 'no' ); ?>> <?php _e( 'No', 'edd_fes' ); ?> </label>
					</div>
				</div>

				<div class="fes-form-rows required-field">
					<label><?php printf( _x( 'Allow %s to Set Names of Options', 'FES uppercase plural setting for vendor', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = true, $uppercase = true ) ); ?></label>
					<div class="fes-form-sub-fields">
						<label><input type="radio" name="<?php echo $names_name; ?>" value="yes"<?php checked( $names, 'yes' ); ?>> <?php _e( 'Yes', 'edd_fes' ); ?> </label>
						<label><input type="radio" name="<?php echo $names_name; ?>" value="no"<?php checked( $names, 'no' ); ?>> <?php _e( 'No', 'edd_fes' ); ?> </label>
					</div>
				</div>

				<div class="fes-form-rows required-field">
					<label><?php printf( _x( 'Allow %s to Set Prices of Options', 'FES uppercase plural setting for vendor', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = true, $uppercase = true ) ); ?></label>
					<div class="fes-form-sub-fields">
						<label><input type="radio" name="<?php echo $prices_name; ?>" value="yes"<?php checked( $prices, 'yes' ); ?>> <?php _e( 'Yes', 'edd_fes' ); ?> </label>
						<label><input type="radio" name="<?php echo $prices_name; ?>" value="no"<?php checked( $prices, 'no' ); ?>> <?php _e( 'No', 'edd_fes' ); ?> </label>
					</div>
				</div>

				<div class="fes-form-rows required-field">
					<label><?php printf( _x( 'Allow %s to Upload Files', 'FES uppercase plural setting for vendor', 'edd_fes' ), EDD_FES()->helper->get_vendor_constant_name( $plural = true, $uppercase = true ) ); ?></label>
					<div class="fes-form-sub-fields">
						<label><input type="radio" name="<?php echo $files_name; ?>" value="yes"<?php checked( $files, 'yes' ); ?>> <?php _e( 'Yes', 'edd_fes' ); ?> </label>
						<label><input type="radio" name="<?php echo $files_name; ?>" value="no"<?php checked( $files, 'no' ); ?>> <?php _e( 'No', 'edd_fes' ); ?> </label>
					</div>
				</div>

				<div class="fes-form-rows">
					<label><?php _e( 'Force Options', 'edd_fes' ); ?></label>

					<div class="fes-form-sub-fields">
						<label><input type="checkbox" class="multicolumn" name="<?php echo $enable_column_name ?>"<?php echo $has_column ? ' checked="checked"' : ''; ?> value="true"> Force Names/Prices</label>
					</div>
				</div>
				<div class="fes-form-rows column-names<?php echo $has_column ? '' : ' fes-hide'; ?>">
					<label><?php _e( 'Force Names/Prices', 'edd_fes' ); ?></label>

					<div class="fes-form-sub-fields">
					<?php
					$add    = fes_assets_url .'img/add.png';
					$remove = fes_assets_url. 'img/remove.png';
					if ( isset( $this->characteristics['columns'] ) && $this->characteristics['columns'] > 0 ) {
						$keys       = count( $this->characteristics['columns'] );
						$new_values = array();
						$key        = 0;
						foreach ( $this->characteristics['columns'] as $old_key => $value ) {
							if ( $old_key === 0 || $old_key % 2 == 0 ) {
								$new_values[$key]['name'] = $value['name'];
							}
							else {
								$new_values[$key]['price'] = $value['price'];
								$key++;
							}
							unset( $this->characteristics['columns'][$old_key] );
						}
						$this->characteristics['columns'] = $new_values;
						foreach ( $this->characteristics['columns'] as $key => $value ) { ?>
							<div>
								<?php _e( 'Name: ', 'edd_fes' ); ?><input type="text" name="<?php echo $column_names; ?>[][name]" value="<?php echo $value['name']; ?>">
								<?php _e( 'Price: ', 'edd_fes' ); ?><input type="text" name="<?php echo $column_names; ?>[][price]" value="<?php echo $value['price']; ?>">
								<img style="cursor:pointer; margin:0 3px;" alt="add another choice" title="add another choice" class="fes-clone-field" src="<?php echo $add; ?>">
								<img style="cursor:pointer;" class="fes-remove-field" alt="remove this choice" title="remove this choice" src="<?php echo $remove; ?>">
							</div>
							<?php
						}
					} else { ?>
							<div>
								<?php _e( 'Name: ', 'edd_fes' ); ?><input type="text" name="<?php echo $column_names; ?>[][name]" value="">
								<?php _e( 'Price: ', 'edd_fes' ); ?><input type="text" name="<?php echo $column_names; ?>[][price]" value="">
								<img style="cursor:pointer; margin:0 3px;" alt="add another choice" title="add another choice" class="fes-clone-field" src="<?php echo $add; ?>">
								<img style="cursor:pointer;" class="fes-remove-field" alt="remove this choice" title="remove this choice" src="<?php echo $remove; ?>">
							</div>
						<?php } ?>
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
		if ( $this->required() ) {
			$names_disabled     = isset( $this->characteristics['names'] )    &&  $this->characteristics['names']    !== 'no'    ? false : true;
			$prices_disabled    = isset( $this->characteristics['prices'] )   &&  $this->characteristics['prices']   !== 'no'    ? false : true;
			$files_disabled     = isset( $this->characteristics['files'] )    &&  $this->characteristics['files']    !== 'no'    ? false : true;
			if ( ! $names_disabled ) {
				$names_disabled     = isset( $this->characteristics['names'] )    &&  $this->characteristics['names']    !== 'no'    ? false : true;
			}
			if ( !empty( $values[ 'option' ] ) ) {
				if ( is_array( $values[ 'option' ] ) ){
					foreach( $values[ 'option' ] as $key => $option  ){
						if ( !$prices_disabled ) {
							if ( !empty( $values[ 'option' ][ $key ]['price'] ) ){
								// price is good
							} else {
								$return_value = __( 'Please fill out all rows completely.', 'edd_fes' );
								break;
							}
						}
						
						if ( $this->characteristics['single'] !== 'yes' && ! $names_disabled ) {
							if ( !empty( $values[ 'option' ][ $key ]['description'] ) ){
								// description is good
							} else {
								$return_value = __( 'Please fill out all rows completely.', 'edd_fes' );
								break;
							}
						}
						
						if ( ! $files_disabled ) {
							if ( !empty( $values[ 'files' ][ $key ] ) ){
								if ( filter_var( $values[ 'files' ][ $key ], FILTER_VALIDATE_URL ) === false ) {
									// if that's not a url
									/** 
									* Since 2.3.11
									* Extensions can hook in here to accept other types of files
									* Simply:
									* add_filter( 'fes_validate_multiple_pricing_field_files', 'your_function', 10, 2 );
									* function your_function( $is_valid, $file ){
									* 	if ( ! $is_valid ){
									*		$is_valid = my_custom_validity_test( $file );
									*       }
									*       return $is_valid;
									*  }
									* @todo: Real docbloc this
									**/
									$valid = apply_filters( 'fes_validate_' . $this->template() . '_field_files', false, $values[ 'files' ][ $key ] );
									if ( ! $valid ) {
										$return_value = __( 'Please enter a valid URL', 'edd_fes' ); 
										break;
									}
								} else {
									// file is good
								}
							} else {
								$return_value = __( 'Please enter a valid URL', 'edd_fes' );
								break;
							}
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
		if ( !empty( $values[ 'option' ] ) ) {
			if ( is_array( $values[ 'option' ] ) ){
				foreach( $values[ 'option' ] as $key => $option  ){
					if ( isset( $option[ 'price' ] ) ) {
						$values[ 'option' ][ $key ]['price'] = edd_sanitize_amount( trim( $values[ 'option' ][ $key ]['price'] ) );
					}
					if ( isset( $option[ 'description' ] ) ){
						$values[ 'option' ][ $key ]['description'] = sanitize_text_field( trim( $values[ 'option' ][ $key ]['description'] ) );
					}
				}
				$values[ $name ]['option'] = $values['option'];
			}
		}

		if ( !empty( $values[ 'files' ] ) ) {
			if ( is_array( $values[ 'files' ] ) ){
				foreach( $values[ 'files' ] as $key => $option  ){
					$values[ 'files' ][ $key ] = filter_var( trim( $values[ 'files' ][ $key ] ), FILTER_SANITIZE_URL );
				}
				$values[ $name ]['files']  = $values['files'];
			}
		}

		return apply_filters( 'fes_sanitize_' . $this->template() . '_field', $values, $name, $save_id, $user_id );
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
						echo '';
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
		$values     = $this->get_field_value_frontend( $this->save_id, $user_id );
		$output    = '';
		return $output;
	}

	public function save_field_admin( $save_id = -2, $value = '', $user_id = -2 ) {
		// Don't save in the backend
	}

	public function save_field_frontend( $save_id = -2, $value = array(), $user_id = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $save_id == -2 || $save_id < 1 ) {
			$save_id = $this->save_id;
		}

		$options   = isset( $value[ 'option' ] ) ? $value[ 'option' ] : '';
		$files     = isset( $value[ 'files' ] ) ? $value[ 'files' ] : '';
		$columns   = array();
		$prices    = array();
		$edd_files = array();
		if ( isset( $options ) && $options != '' ) {
			$counter = 0;
			foreach ( $options as $key => $option ) {
				$columns['name']   = isset( $option[ 'description' ] ) ? sanitize_text_field( $option[ 'description' ] ) : '';
				$columns['amount'] = isset( $option[ 'price' ] ) ? $option[ 'price' ] : '';
				$prices[$counter]  = apply_filters( 'fes_price_option_pre_save_values', $columns, $option );
				$counter++;
			}
			if ( !empty( $files ) ) {
				foreach ( $files as $key => $url ) {
					$edd_files[ $key ] = array(
						'name'      => basename( $url ),
						'file'      => $url,
						'condition' => $key
					);
				}
			}
		} else if ( !empty( $files ) ) {
			// For when there are no prices or option names allowed, https://github.com/chriscct7/edd-fes/issues/417
			foreach ( $files as $key => $url ) {
				$edd_files[ $key ] = array(
					'name'      => basename( $url ),
					'file'      => $url,
					'condition' => $key
				);
			}
		}

		do_action( 'fes_submission_form_save_custom_fields', $save_id );
		if ( count( $prices ) === 1 || count( $prices ) === 0 ) {
			if ( !isset( $prices[ 0 ][ 'amount' ] ) ) {
				$prices[ 0 ][ 'amount' ] = "";
			}
			update_post_meta( $save_id, '_variable_pricing', 0 );
			update_post_meta( $save_id, 'edd_price', $prices[ 0 ][ 'amount' ] );
			update_post_meta( $save_id, 'edd_variable_prices', $prices ); // Save variable prices anyway so that price options are saved
		} else {
			update_post_meta( $save_id, '_variable_pricing', 1 );
			update_post_meta( $save_id, 'edd_variable_prices', $prices );
			if ( EDD_FES()->helper->get_option( 'fes-allow-multiple-purchase-mode', false ) ) {
				update_post_meta( $save_id, '_edd_price_options_mode', '1' );
			}
		}

		$save_files = false;
		if ( $files && is_array( $files ) && !empty( $files ) ) {
			foreach ( $files as $key => $url ) {
				if ( !empty( $url ) ) {
					$save_files = true;
					break;
				}
			}
		}

		if ( !empty( $files ) && $save_files ) {
			$edd_files = apply_filters( 'fes_pre_files_save', $edd_files, $save_id );
			update_post_meta( $save_id, 'edd_download_files', $edd_files );
		} else {
			update_post_meta( $save_id, 'edd_download_files', array() );
		}
	}

	/** Gets field value for admin */
	public function get_field_value_admin( $save_id = -2, $user_id = -2, $public = -2 ) {
		// Don't get field value in the backend
		return false;
	}

	/** Gets field value for frontend */
	public function get_field_value_frontend( $save_id = -2, $user_id = -2, $public = -2 ) {
		// Don't get field value in the frontend
		return false;
	}
}
