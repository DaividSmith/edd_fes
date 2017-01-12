;(function ($) {
	var product_featured_frame;
	var avatar_frame;
	var FES_Form = {
		init: function () {
			// clone and remove repeated field
			$('.fes-form').on('click', 'img.fes-clone-field', this.cloneField);
			$('.fes-form').on('click', 'img.fes-remove-field', this.removeField);

			// form submissions
			$('.fes-ajax-form').on('submit', this.formSubmit);

			// featured image
			$('.fes-fields').on('click', 'a.fes-feat-image-btn', this.featuredImage.addImage);
			$('.fes-fields').on('click', 'a.fes-remove-feat-image', this.featuredImage.removeImage);

			// featured image
			$('.fes-fields').on('click', 'a.fes-avatar-image-btn', this.avatarImage.addImage);
			$('.fes-fields').on('click', 'a.fes-remove-avatar-image', this.avatarImage.removeImage);

			// download links
			$('.fes-fields').on('click', 'a.upload_file_button', this.fileDownloadable);

			// Repeatable file inputs
			$('.fes-fields').on('click', 'a.insert-file-row', function (e) {
				e.preventDefault();
				var clickedID = $(this).attr('id');
				var max = $('#fes-upload-max-files-'+clickedID ).val();
				var optionContainer = $('.fes-variations-list-'+clickedID);
				var option = optionContainer.find('.fes-single-variation:last');
				var newOption = option.clone();
				delete newOption[1];
				newOption.length = 1;
				var count = optionContainer.find('.fes-single-variation').length;

				// too many files 
				if ( count + 1 > max && max != 0 ){
					return alert(fes_form.too_many_files_pt_1 + max + fes_form.too_many_files_pt_2);
				}

				newOption.find('input, select, textarea').val('');
				newOption.find('input, select, textarea').each(function () {
					var name = $(this).attr('name');
					name = name.replace(/\[(\d+)\]/, '[' + parseInt(count) + ']');
					$(this)
						.attr('name', name)
						.attr('id', name);

					newOption.insertBefore("#"+clickedID);
				});
				return false;
			});


			$('.fes-fields').on('click', 'a.edd-fes-delete', function (e) {
				e.preventDefault();
				var option = $(this).parents('.fes-single-variation');
				var optionContainer = $(this).parents('[class^=fes-variations-list-]');
				var count = optionContainer.find('.fes-single-variation').length;

				if (count == 1) {
					option.find('input, select, textarea').val('');
					return false;
				} else {
					option.remove();
					return false;
				}
			});
		},

		avatarImage: {

			addImage: function (e) {
				e.preventDefault();

				var self = $(this);

				if (avatar_frame) {
					avatar_frame.open();
					return;
				}

				avatar_frame = wp.media({
					title: fes_form.avatar_title,
					button: {
						text: fes_form.avatar_button
					},
					library: {
						type: 'image'
					}
				});

				avatar_frame.on('select', function () {
					var selection = avatar_frame.state().get('selection');

					selection.map(function (attachment) {
						attachment = attachment.toJSON();

						// set the image hidden id
						self.siblings('input.fes-avatar-image-id').val(attachment.id);

						// set the image
						var instruction = self.closest('.instruction-inside');
						var wrap = instruction.siblings('.image-wrap');

						// wrap.find('img').attr('src', attachment.sizes.thumbnail.url);
						wrap.find('img').attr('src', attachment.url);

						instruction.addClass('fes-hide');
						wrap.removeClass('fes-hide');
					});
				});

				avatar_frame.open();
			},

			removeImage: function (e) {
				e.preventDefault();

				var self = $(this);
				var wrap = self.closest('.image-wrap');
				var instruction = wrap.siblings('.instruction-inside');

				instruction.find('input.fes-avatar-image-id').val('0');
				wrap.addClass('fes-hide');
				instruction.removeClass('fes-hide');
			}
		},

		fileDownloadable: function (e) {
			e.preventDefault();

			var self = $(this),
				downloadable_frame;

			if (downloadable_frame) {
				downloadable_frame.open();
				return;
			}

			downloadable_frame = wp.media({
				title: fes_form.file_title,
				button: {
					text: fes_form.file_button
				},
				multiple: false
			});

			downloadable_frame.on('open',function() {
				// turn on file filter
				$.post(fes_form.ajaxurl,{ action:'fes_turn_on_file_filter'}, function (res) { });
			});

			downloadable_frame.on('close',function() {
				// turn on file filter
				$.post(fes_form.ajaxurl,{ action:'fes_turn_off_file_filter'}, function (res) { });
			});

			downloadable_frame.on('select', function () {
				var selection = downloadable_frame.state().get('selection');

				selection.map(function (attachment) {
					attachment = attachment.toJSON();

					self.closest('tr').find('input.fes-file-value').val(attachment.url);
				});
			});

			downloadable_frame.open();
		},


		featuredImage: {

			addImage: function (e) {
				e.preventDefault();

				var self = $(this);

				if (product_featured_frame) {
					product_featured_frame.open();
					return;
				}

				product_featured_frame = wp.media({
					title: fes_form.feat_title,
					button: {
						text: fes_form.feat_button
					},
					library: {
						type: 'image'
					}
				});

				product_featured_frame.on('select', function () {
					var selection = product_featured_frame.state().get('selection');

					selection.map(function (attachment) {
						attachment = attachment.toJSON();

						// set the image hidden id
						self.siblings('input.fes-feat-image-id').val(attachment.id);

						// set the image
						var instruction = self.closest('.instruction-inside');
						var wrap = instruction.siblings('.image-wrap');

						// wrap.find('img').attr('src', attachment.sizes.thumbnail.url);
						wrap.find('img').attr('src', attachment.url);

						instruction.addClass('fes-hide');
						wrap.removeClass('fes-hide');
					});
				});

				product_featured_frame.open();
			},

			removeImage: function (e) {
				e.preventDefault();

				var self = $(this);
				var wrap = self.closest('.image-wrap');
				var instruction = wrap.siblings('.instruction-inside');

				instruction.find('input.fes-feat-image-id').val('0');
				wrap.addClass('fes-hide');
				instruction.removeClass('fes-hide');
			}
		},

		cloneField: function (e) {
			e.preventDefault();

			var $div = $(this).closest('tr');
			var $clone = $div.clone();
			var $trs = $div.parent().find('tr');

			var key = highest = 0;
			$trs.each(function() {
				var current = $(this).data( 'key' );
				if ( parseInt( current ) > highest ) {
					highest = current;
				}
			});
			key = highest + 1;

			//clear the inputs
			$clone.attr( 'data-key', parseInt( key ) );
			$clone.find(':checked').attr('checked', '');
			$clone.find('input, select, textarea').val('');
			$clone.find('input, select, textarea').each(function () {
				var name = $(this).attr('name');
				name = name.replace(/\[(\d+)\]/, '[' + parseInt(key) + ']');
				$(this).attr('name', name).attr('id', name);
			});

			$div.after($clone);
		},

		removeField: function () {
			//check if it's the only item
			var $parent = $(this).closest('tr');
			var items = $parent.siblings().andSelf().length;

			if (items > 1) {
				$parent.remove();
			}
		},

		formSubmit: function (e) {
			e.preventDefault();

			var form = $(this),
				submitButton = form.find('input[type=submit]'),
				form_data = FES_Form.validateForm(form);

			if (form_data) {
				// send the request
				var opts = {
					lines: 13, // The number of lines to draw
					length: 11, // The length of each line
					width: 5, // The line thickness
					radius: 17, // The radius of the inner circle
					corners: 1, // Corner roundness (0..1)
					rotate: 0, // The rotation offset
					color: '#FFF', // #rgb or #rrggbb
					speed: 1, // Rounds per second
					trail: 60, // Afterglow percentage
					shadow: false, // Whether to render a shadow
					hwaccel: false, // Whether to use hardware acceleration
					className: 'fes_spinner', // The CSS class to assign to the spinner
					zIndex: 2e9, // The z-index (defaults to 2000000000)
					top: 'auto', // Top position relative to parent in px
					left: 'auto' // Left position relative to parent in px
				};
				if ( fes_form.loading_icon !== "" ){
					opts['icon'] = fes_form.loading_icon;
				}
				
				var target = document.createElement("div");
				document.body.appendChild(target);
				
				if ( fes_form.loading_icon != "" ){
					var overlay = fesSpinner({
						text: fes_form.loadingtext,
						icon: fes_form.loading_icon
					});
				} else {
					var spinner = new Spinner(opts).spin(target);
					var overlay = fesSpinner({
						text: fes_form.loadingtext,
						spinner: spinner
					});
				}					
				
				submitButton.attr('disabled', 'disabled').addClass('button-primary-disabled');
				$.post(fes_form.ajaxurl, form_data, function (res) {
					//if ( window.console && window.console.log ) {
					//    console.log( res );
					//}
					if (res.success) {
						var title = '';
						var message = '';
						if ( res.title ){
							title = res.title;
						} else{
							title = fes_form.successtitle;
						}
						if ( res.message ){
							message = res.message;
						} else{
							message = fes_form.successmessage;
						}
						if ( res.skipswal ){
							overlay.hide();
							 if ( res.redirect_to !== '#' ){
									window.location = res.redirect_to;
								}
						} else {
							overlay.hide();
							swal({ 
								title: title,
								text: message,
								html: true,
								allowEscapeKey : false,
								type: "success"
							},
							function(){
								if ( res.redirect_to !== '#' ){
										window.location.href = res.redirect_to;
								} else {
									submitButton.removeClass('button-primary-disabled');
									form.find('span.fes-loading').remove();
									submitButton.removeAttr('disabled'); // undisable the submit button
								}
							} );
						}
					} else {
						var errors = res.errors;
						if (typeof errors !== 'undefined' && FES_Form.hasItems(errors) > 0) {
							// foreach error as error
							for (var key in errors ) {
								// inject error message
								FES_Form.markError( form, key, errors[key] );
							}
						}
						var title = '';
						var message = '';
						if ( res.title ){
							title = res.title;
						} else{
							title = fes_form.errortitle;
						}
						if ( res.message ){
							message = res.message;
						} else{
							message = fes_form.errormessage;
						}
						overlay.hide(); // hide loading overlay

						// show error overlay
						swal({
							title: title,
							text: message,
							html: true,
							type: "error"
						});

						submitButton.removeAttr('disabled'); // undisable the submit button
					}
					submitButton.removeClass('button-primary-disabled');
					form.find('span.fes-loading').remove();
				})
				.fail( function(xhr, textStatus, errorThrown) {
						var title = '';
						var message = '';
						title = fes_form.ajaxerrortitle;
						message = $(xhr.responseText).text();
						console.log( message );
						message = message.substring(0, message.indexOf("Call Stack"));
						overlay.hide(); // hide loading overlay
						// show error overlay
						swal({
							title: title,
							text: message,
							html: true,
							type: "error"
						}); 
						submitButton.removeAttr('disabled'); // undisable the submit button
						submitButton.removeClass('button-primary-disabled');
						form.find('span.fes-loading').remove();
				});                    
			}
		},
		
		hasItems: function (map) {
		   for(var key in map) {
			  if (map.hasOwnProperty(key)) {
				 return true;
			  }
		   }
		   return false;
		},

		validateForm: function (self) {
			FES_Form.removeErrors(self);

			var temp,
				form_data = self.serialize(),
				rich_texts = [];

			// grab rich texts from tinyMCE
			$('.fes-rich-validation').each(function (index, item) {
				temp = $(item).data('id');
				val = $.trim(tinyMCE.get(temp).getContent());
				rich_texts.push(temp + '=' + encodeURIComponent(val));
			});

			// append them to the form var
			form_data = form_data + '&' + rich_texts.join('&');
			return form_data;
		},

		markError: function ( form, name, message ) {
			var field = form.find( '.fes-el.' + name );
			field.append('<div class="edd_errors edd-alert edd-alert-error fes-form-field-error" style="margin-bottom: 0px"><p class="edd_error">' + message + "</p></div>");
		},

		removeErrors: function (item) {
			$(item).find('.fes-form-field-error').remove();
		}
	};

	$(function () {
		FES_Form.init();
	});

})(jQuery);
