jQuery(document).ready(function($) {
	// Tooltips on the dashboard icons
	$( ".tips, .help_tip" ).tipTip({
		'attribute' : 'data-tip',
		'fadeIn' : 50,
		'fadeOut' : 50,
		'delay' : 200
	});

	/**
	 * Vendor management screen JS
	 */
	var FES_Vendor = {

		init : function() {
			this.edit_vendor();
			this.user_search();
			this.cancel_edit();
			this.change_country();
			this.add_note();
		},
		edit_vendor: function() {
			$( 'body' ).on( 'click', '#edit-vendor', function( e ) {
				e.preventDefault();
				$( '#edd-vendor-card-wrapper .editable' ).hide();
				$( '#edd-vendor-card-wrapper .edit-item' ).fadeIn().css( 'display', 'block' );
			});
		},
		user_search: function() {
			// Upon selecting a user from the dropdown, we need to update the User ID
			$('body').on('click.eddSelectUser', '.edd_user_search_results a', function( e ) {
				e.preventDefault();
				var user_id = $(this).data('userid');
				$('input[name="vendorinfo[user_id]"]').val(user_id);
			});
		},
		cancel_edit: function() {
			$( 'body' ).on( 'click', '#edd-edit-vendor-cancel', function( e ) {
				e.preventDefault();
				$( '#edd-vendor-card-wrapper .edit-item' ).hide();
				$( '#edd-vendor-card-wrapper .editable' ).show();
				$( '.edd_user_search_results' ).html('');
			});
		},
		change_country: function() {
			$('select[name="vendorinfo[country]"]').change(function() {
				var $this = $(this);
				data = {
					action: 'edd_get_shop_states',
					country: $this.val(),
					field_name: 'vendorinfo[state]'
				};
				$.post(ajaxurl, data, function (response) {
					if ( 'nostates' == response ) {
						$(':input[name="vendorinfo[state]"]').replaceWith( '<input type="text" name="' + data.field_name + '" value="" class="edd-edit-toggles medium-text"/>' );
					} else {
						$(':input[name="vendorinfo[state]"]').replaceWith( response );
					}
				});

				return false;
			});
		},
		add_note : function() {
			$( 'body' ).on( 'click', '#add-vendor-note', function( e ) {
				e.preventDefault();
				var postData = {
					edd_action : 'add-vendor-note',
					vendor_id : $( '#vendor-id' ).val(),
					vendor_note : $( '#vendor-note' ).val(),
					add_vendor_note_nonce: $( '#add_vendor_note_nonce' ).val()
				};

				if ( postData.vendor_note ) {

					$.ajax({
						type: "POST",
						data: postData,
						url: ajaxurl,
						success: function ( response ) {
							$( '#edd-vendor-notes' ).prepend( response );
							$( '.edd-no-vendor-notes' ).hide();
							$( '#vendor-note' ).val( '' );
						}
					}).fail( function ( data ) {
						if ( window.console && window.console.log ) {
							console.log( data );
						}
					});

				} else {
					var border_color = $( '#vendor-note' ).css( 'border-color' );
					$( '#vendor-note' ).css( 'border-color', 'red' );
					setTimeout( function() {
						$( '#vendor-note' ).css( 'border-color', border_color );
					}, 500 );
				}
			});
		}

	};
	FES_Vendor.init();
	
	$('.vendor-change-status').on('click', function (e) {
		e.preventDefault();

		var vendor = $(this).data('vendor');
		var status = $(this).data('status');
		var nstatus = $(this).data('nstatus');
		nstatus = nstatus.toLowerCase();

		swal({
			title: fes_admin.vendor_status_change_title,
			text: fes_admin.vendor_status_change_message_start + nstatus + fes_admin.vendor_status_change_message_end,
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: '#DD6B55',
			confirmButtonText: fes_admin.vendor_status_change_yes,
			cancelButtonText: fes_admin.vendor_status_change_no,
			closeOnConfirm: false,
			html: true,
			closeOnCancel: true
		},

		function( confirm ) {
			if (confirm){
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

				var target = document.createElement("div");
				document.body.appendChild(target);
				if ( fes_admin.loading_icon != "" ){
					var overlay = fesSpinner({
						text: fes_admin.loadingtext,
						icon: fes_admin.loading_icon
					});
				} else {
					var spinner = new Spinner(opts).spin(target);
					var overlay = fesSpinner({
						text: fes_admin.loadingtext,
						spinner: spinner
					});
				}				
				
				$.post( fes_admin.ajaxurl, { action:'fes_change_vendor_status', vendor: vendor, status: status, output: true}, function (res) {
					if ( window.console && window.console.log ) {
						console.log( res );
					};
					if (res.success) {
						overlay.hide();
						swal({ 
							title: res.title,
							text: res.message,
							html: true,
							type: "success"
						},
						function(){
							if ( res.redirect_to !== '#' ){
									window.location.href = res.redirect_to;
							} else{
									window.location.reload(true);
							}
						} );     
					} else {
						overlay.hide();
						// show error overlay
						swal({
							title: res.title,
							text: res.message,
							html: true,
							type: "error"
						});
					}
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
				});
			}
		});
	});

	$('.create-vendor-user-edit').on('click', function (e) {
		e.preventDefault();

		var vendor = $(this).data('vendor');
		var nstatus = $(this).data('nstatus');
		nstatus = nstatus.toLowerCase();
		var swaltext = '';
		if ( nstatus == 'create' ){
			swaltext = fes_admin.vendor_status_create_vendor;
		} else {
			swaltext = fes_admin.vendor_status_change_message_start + nstatus + fes_admin.vendor_status_change_message_end;
		}

		swal({
			title: fes_admin.vendor_status_change_title,
			text: swaltext,
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: '#DD6B55',
			confirmButtonText: fes_admin.vendor_status_change_yes,
			cancelButtonText: fes_admin.vendor_status_change_no,
			closeOnConfirm: false,
			html: true,
			closeOnCancel: true
		},

		function( confirm ) {
			if (confirm){
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
				
				
				var target = document.createElement("div");
				document.body.appendChild(target);
				if ( fes_admin.loading_icon != "" ){
					var overlay = fesSpinner({
						text: fes_admin.loadingtext,
						icon: fes_admin.loading_icon
					});
				} else {
					var spinner = new Spinner(opts).spin(target);
					var overlay = fesSpinner({
						text: fes_admin.loadingtext,
						spinner: spinner
					});
				}
				

				$.post( fes_admin.ajaxurl, { action:'fes_create_vendor_user_edit', vendor: vendor }, function (res) {
					if ( window.console && window.console.log ) {
						console.log( res );
					};
					if (res.success) {
						overlay.hide();
						var swaltextsuccess = '';
						if ( nstatus == 'create' ){
							swaltextsuccess = fes_admin.vendor_status_create_vendor_success;
						} else {
							swaltextsuccess = res.message;
						}
						// show error overlay
						swal({
							title: res.title,
							text: swaltextsuccess,
							html: true,
							type: "success"
						},
						function(){
							if ( res.redirect_to !== '#' ){
									window.location.href = res.redirect_to;
							} else{
									window.location.reload(true);
							}
						} );          
					} else {
						overlay.hide();
						swal({
							title: res.title,
							text: res.message,
							html: true,
							type: "error"
						});
					}
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
				});
			}
		});
	});

});
