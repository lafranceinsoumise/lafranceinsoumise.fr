jQuery( document ).ready( function($) {

	function setup_site( _this ) {
		var site_box = _this.closest( '.site-box' );
		var site_data = site_box.data( 'site-data' );

		if ( ! site_box.hasClass( 'data-loaded' ) ) {
			// Prevent duplicate setup.
			site_box.addClass( 'data-loaded' );

			$.ajax({
				type: 'POST',
				url: generate_sites_params.ajaxurl,
				data: {
					action: 'generate_setup_site_' + site_data.slug,
					nonce: generate_sites_params.nonce,
				},
				success: function( data ) {
					console.log(data);

					if ( data.options ) {
						site_box.find( '.theme-options.loading' ).hide();
						site_box.find( '.theme-options.loading' ).next().text( generate_sites_params.theme_options_exist ).fadeIn();
					} else {
						site_box.find( '.theme-options.loading' ).hide();
						site_box.find( '.theme-options' ).next().text( generate_sites_params.no_theme_options ).fadeIn();
						site_box.find( '.step-overview .right button' ).attr( 'disabled', 'disabled' );
						site_box.find( '.site-content-description' ).remove();
					}

					if ( data.content ) {
						site_box.find( '.site-content.loading' ).hide();
						site_box.find( '.site-content.loading' ).next().text( generate_sites_params.site_content_exists ).fadeIn();

						if ( data.plugins ) {
							site_box.find( '.plugin-area' ).fadeIn();
						}
					} else {
						site_box.find( '.site-content-description' ).remove();
					}

					if ( data.modules ) {
						$.each( data.modules, function( name, key ) {
							site_box.find( '.required-modules ul' ).append( '<li>' + name + '</li>' );
						} );
					} else {
						site_box.find( '.required-modules' ).hide();
					}

					if ( data.widgets ) {
						site_box.find( '.site-content-description .site-action' ).attr( 'data-widgets', true );
					} else {
						site_box.find( '.site-content-description .site-action' ).attr( 'data-widgets', false );
					}

					if ( data.plugins ) {
						$.ajax( {
							type: 'POST',
							url: generate_sites_params.ajaxurl,
							data: {
								action: 'generate_check_plugins_' + site_data.slug,
								nonce: generate_sites_params.nonce,
								data: site_box.data( 'site-data' ),
							},
							success: function( data ) {
								console.log( data );
								site_box.find( '.checking-for-plugins.loading' ).hide();
								site_box.find( '.site-content-description .site-action' ).attr( 'data-plugins', JSON.stringify( data.plugin_data ) );

								$.each( data.plugin_data, function( index, value ) {
									var slug = value.slug.substring( 0, value.slug.indexOf( '/' ) );

									if ( value.repo && ! value.installed ) {
										site_box.find( '.automatic-plugins' ).fadeIn();
										site_box.find( '.automatic-plugins ul' ).append( '<li data-slug="' + slug + '">' + value.name + '</li>' );
									} else if ( value.installed || value.active ) {
										site_box.find( '.installed-plugins' ).fadeIn();
										site_box.find( '.installed-plugins ul' ).append( '<li class="plugin-installed" data-slug="' + slug + '">' + value.name + '</li>' );
									} else {
										site_box.find( '.manual-plugins' ).fadeIn();
										site_box.find( '.manual-plugins ul' ).append( '<li>' + value.name + '</li>' );
									}
								} );

								site_box.find( '.step-overview .loading' ).hide();
								site_box.find( '.step-overview .right button' ).show();
							},
							error: function( data ) {
								console.log( data );
								_this.closest( '.site-box' ).find( '.site-message' ).hide();
								_this.closest( '.site-box' ).find( '.error-message' ).text( data.status + ' ' + data.statusText ).show();
							}
						} );
					} else {
						site_box.find( '.step-overview .loading' ).hide();
						site_box.find( '.step-overview .right button' ).show();
					}
				},
				error: function( data ) {
					console.log( data );
					site_box.removeClass( 'data-loaded' );
					_this.closest( '.site-box' ).find( '.loading' ).hide();
					_this.closest( '.site-box' ).find( '.error-message' ).text( data.status + ' ' + data.statusText ).show();
				}
			});
		}
	}

	var bLazy = new Blazy({
	    selector: '.lazyload',
		success: function(ele){
            $( ele ).parent().addClass( 'image-loaded' );
        }
	});

	/**
	 * Demo sites
	 */
	$( '.preview-site' ).on( 'click', function( e ) {
		e.preventDefault();
		var _this = $( this );
		var site_box = _this.closest( '.site-box' );

		if ( ! site_box.find( 'iframe' ).attr( 'src' ) ) {
			site_box.find( 'iframe' ).attr( 'src', site_box.data( 'site-data' ).preview_url );
		}

		site_box.find( 'iframe' ).on( 'load', function () {
			site_box.find( '.demo-loading' ).fadeOut().remove();
		});

		site_box.find( '.site-demo' ).show().addClass( 'open' );
		setup_site( _this );
	} );

	$( '.site-demo .close-demo' ).on( 'click', function( e ) {
		$( '.site-demo' ).hide().removeClass( 'open' );
		bLazy.revalidate();
	} );

	$( '.demo-panel .show-desktop' ).on( 'click', function( e ) {
		$( this ).addClass( 'active' ).siblings().removeClass( 'active' );
		$( '.site-demo' ).removeClass( 'mobile' ).removeClass( 'tablet' );
	} );

	$( '.demo-panel .show-tablet' ).on( 'click', function( e ) {
		$( this ).addClass( 'active' ).siblings().removeClass( 'active' );
		$( '.site-demo' ).removeClass( 'mobile' ).addClass( 'tablet' );
	} );

	$( '.demo-panel .show-mobile' ).on( 'click', function( e ) {
		$( this ).addClass( 'active' ).siblings().removeClass( 'active' );
		$( '.site-demo' ).addClass( 'mobile' ).removeClass( 'tablet' );
	} );

	$( '.site-demo .get-started' ).on( 'click', function( e ) {
		$( '.site-demo' ).hide().removeClass( 'open' );

		if ( ! $( '.generatepress-sites' ).hasClass( 'site-open' ) ) {
			$( '.generatepress-sites' ).addClass( 'site-open' );
			$( '.page-builder-group' ).hide();
			$( this ).closest( '.site-box' ).siblings().hide();
			$( this ).closest( '.site-box' ).find( '.step-one' ).hide().next().show();
		}
	} );

	/**
	 * Site card controls
	 */
	$( '.site-box .close' ).on( 'click', function( e ) {
		e.preventDefault();
		var site = $( '.site-box' );
		var page_builder = $( '.generatepress-sites' ).attr( 'data-page-builder' );

		site.find( '.steps' ).hide();
		site.find( '.step-one' ).fadeIn().css( 'display', '' );
		$( '.generatepress-sites .complete' ).hide();
		$( '.site-action:not(.import-options)' ).show();
		site.find( '.error-message' ).hide();

		$( '.site-action.import-content' ).attr( 'disabled', 'disabled' );
		$( '.confirm-content-import' ).prop( 'checked', false );

		$( '.generatepress-sites' ).removeClass( 'site-open' );
		$( '.page-builder-group' ).show();
		$( this ).closest( '.site-box' ).siblings( page_builder ).fadeIn( 'fast' );

		bLazy.revalidate();
	} );

	$( '.site-box .next' ).on( 'click', function( e ) {
		var page_builder = $( '.generatepress-sites' ).attr( 'data-page-builder' );
		var this_site = $( this ).closest( '.site-box' );
		var next_site = this_site.nextAll( page_builder ).not( '.disabled-site' ).first();

		if ( ! next_site.length ) {
			next_site = $( '.generatepress-sites' ).find( '.site-box' + page_builder ).first();
		}

		if ( this_site.parent().hasClass( 'site-open' ) ) {
			this_site.hide();
			next_site.show().find( '.step-one' ).hide().next().show();

			setup_site( next_site );
		}

		if ( this_site.find( '.site-demo' ).hasClass( 'open' ) ) {
			this_site.find( '.site-demo' ).hide().removeClass( 'open' );

			if ( ! next_site.find( 'iframe' ).attr( 'src' ) ) {
				next_site.find( 'iframe' ).attr( 'src', next_site.data( 'site-data' ).preview_url );
			}

			next_site.find( 'iframe' ).on( 'load', function () {
				next_site.find( '.demo-loading' ).fadeOut().remove();
			});

			next_site.find( '.site-demo' ).show().addClass( 'open' );

			setup_site( next_site );
		}
	} );

	$( '.site-box .prev' ).on( 'click', function( e ) {
		var page_builder = $( '.generatepress-sites' ).attr( 'data-page-builder' );
		var this_site = $( this ).closest( '.site-box' );
		var prev_site = this_site.prevAll( page_builder ).not( '.disabled-site' ).first();

		if ( ! prev_site.length ) {
			prev_site = $( '.generatepress-sites' ).find( '.site-box' + page_builder ).last();
		}

		if ( this_site.parent().hasClass( 'site-open' ) ) {
			this_site.hide();
			prev_site.show().find( '.step-one' ).hide().next().show();

			setup_site( prev_site );
		}

		if ( this_site.find( '.site-demo' ).hasClass( 'open' ) ) {
			this_site.find( '.site-demo' ).hide().removeClass( 'open' );

			if ( ! prev_site.find( 'iframe' ).attr( 'src' ) ) {
				prev_site.find( 'iframe' ).attr( 'src', prev_site.data( 'site-data' ).preview_url );
			}

			prev_site.find( 'iframe' ).on( 'load', function () {
				prev_site.find( '.demo-loading' ).fadeOut().remove();
			});

			prev_site.find( '.site-demo' ).show().addClass( 'open' );

			setup_site( prev_site );
		}
	} );

	$( '.site-details' ).on( 'click', function( e ) {
		var _this = $( this );

		setup_site( _this );

		$( '.generatepress-sites' ).addClass( 'site-open' );
		$( '.page-builder-group' ).hide();
		_this.closest( '.site-box' ).siblings().hide();
		var step = _this.closest( '.steps' );
		step.hide();
		step.next().fadeIn( 'fast' );
	} );

	$( '.next-step' ).on( 'click', function( e ) {
		e.preventDefault();
		var step = $( this ).closest( '.steps' );
		step.hide();
		step.next().show();
	} );

	$( '.start-over' ).on( 'click', function( e ) {
		e.preventDefault();
		var site = $( this ).closest( '.site-box' );
		site.find( '.steps' ).hide();
		site.find( '.step-one' ).next().show();
		$( '.generatepress-sites .complete' ).hide();
		$( '.site-action.import-options' ).hide();
		$( '.site-action:not(.import-options)' ).show();
		site.find( '.error-message' ).hide();

		$( '.site-action.import-content' ).attr( 'disabled', 'disabled' );
		$( '.confirm-content-import' ).prop( 'checked', false );
	} );

	$( '.confirm-content-import' ).on( 'change', function() {
		if ( $( this ).is( ':checked' ) ) {
			$( 'input.import-content' ).attr( 'disabled', false );
			$( '.confirm-content-replace-container' ).show();
		} else {
			$( 'input.import-content' ).attr( 'disabled', 'disabled' );
			$( '.confirm-content-replace-container' ).hide();
		}
	} );

	$( '.page-builder-group a' ).on( 'click', function( e ) {
		e.preventDefault();

		var _this = $( this ),
			filter = _this.data( 'filter' );

		_this.siblings().removeClass( 'active' );
		_this.addClass( 'active' );

		if ( '' == filter ) {
			$( '.site-box' ).show();
			$( '.generatepress-sites' ).attr( 'data-page-builder', '' );
		} else {
			$( '.site-box:not(.' + filter + ')' ).hide();
			$( '.site-box.' + filter ).show();
			$( '.generatepress-sites' ).attr( 'data-page-builder', '.' + filter );
		}

		bLazy.revalidate();
	} );

	/**
	 * Backup options.
	 */
	$( '.backup-options' ).on( 'click', function(e) {
		e.preventDefault();
		var _this = $( this );
		_this.hide();
		_this.siblings( '.loading' ).show();

		backup_options( _this );
	} );

	/**
	 * Backup and import theme options.
	 */
	$( '.import-options' ).on( 'click', function(e) {
		e.preventDefault();
		var _this = $( this );
		_this.hide();
		_this.next( '.loading' ).show();

		import_options( _this );
	} );

	function backup_options( _this ) {
		_this.closest( '.site-overview-details' ).find( '.site-message' ).text( generate_sites_params.backing_up_options ).show();
		var data = _this.closest( '.site-box' ).data( 'site-data' );

		$.ajax( {
			type: 'POST',
			url: generate_sites_params.ajaxurl,
			data: {
				action: 'generate_backup_options_' + data.slug,
				nonce: generate_sites_params.nonce,
			},
			success: function( data ) {
				download( data, 'generatepress-options-backup.json', 'application/json' );

				_this.siblings( '.loading' ).hide();
				_this.siblings( '.loading' ).next( '.complete' ).fadeIn();

				setTimeout( function() {
					_this.siblings( '.loading' ).next( '.complete' ).hide();
					_this.next( 'input' ).show();
				}, 500 );
			},
			error: function( data ) {
				console.log( data );
				_this.closest( '.site-box' ).find( '.loading' ).hide();
				_this.closest( '.site-box' ).find( '.error-message' ).text( data.status + ' ' + data.statusText ).show();
			}
		} );
	}

	function import_options( _this ) {
		_this.closest( '.site-overview-details' ).find( '.site-message' ).text( generate_sites_params.importing_options ).show();
		var data = _this.closest( '.site-box' ).data( 'site-data' );

		$.ajax( {
			type: 'POST',
			url: generate_sites_params.ajaxurl,
			data: {
				action: 'generate_import_options_' + data.slug,
				nonce: generate_sites_params.nonce,
			},
			success: function( data ) {
				console.log( 'Options imported.' );
				_this.hide();
				_this.next( '.loading' ).hide();
				_this.next( '.loading' ).next( '.complete' ).fadeIn();

				setTimeout( function() {
					_this.closest( '.steps' ).hide().next( '.steps' ).show();
				}, 1000 );
			},
			error: function( data ) {
				console.log( data );
				_this.closest( '.site-box' ).find( '.loading' ).hide();
				_this.closest( '.site-box' ).find( '.error-message' ).text( data.status + ' ' + data.statusText ).show();
			}
		} );
	}

	/**
	 * Install and activate plugins.
	 * Before content, as content may be depedent on plugins.
	 */
	$( '.site-box' ).on( 'click', '.import-content', function(e) {
		e.preventDefault();
		var _this = $( this );
		_this.hide();
		_this.next( '.loading' ).show();

		var plugins = _this.data( 'plugins' );

		_this.closest( '.site-box' ).attr( 'data-plugins', JSON.stringify( plugins ) );

		var plugin_text = _this.closest( '.site-box' ).find( '.automatic-plugins li' );

		if ( ! $.isEmptyObject( plugins ) ) {
			_this.closest( '.site-box' ).find( '.site-message' ).text( generate_sites_params.installing_plugins ).show();

			$.each( plugins, function( index, value ) {
				var plugin_slug = value.slug.split('/')[0];

				var plugin_row = plugin_text.filter( function () {
					return $( this ).attr( 'data-slug' ) == plugin_slug;
				} );

				if ( ! value.installed ) {
					plugin_row.find( '.loading' ).show();
					plugin_row.addClass( 'installing-plugins' );

					// Install BB Lite if Pro doesn't exist.
					if ( 'bb-plugin' == plugin_slug ) {
						plugin_slug = 'beaver-builder-lite-version';
					}

					wp.updates.installPlugin( {
						slug: plugin_slug,
						success: function( data ) {
							console.log( data );

							plugin_row.removeClass( 'installing-plugins' ).addClass( 'plugin-installed' );
							plugin_row.removeClass( 'show-loading' ).next().addClass( 'show-loading' );

							// Remove current plugin from queue
							delete plugins[index];

							if ( $.isEmptyObject( plugins ) ) {
								// Onto the next step
								activate_plugins( _this );
							}
						},
						error: function( data ) {
							console.log(data);

							plugin_row.append( '<span class="plugin-error">' + data.errorMessage + '</span>' );
							plugin_row.removeClass( 'installing-plugins' ).addClass( 'plugin-install-failed' );
							plugin_row.removeClass( 'show-loading' ).next().addClass( 'show-loading' );

							// Remove current plugin from queue
							delete plugins[index];

							if ( $.isEmptyObject( plugins ) ) {
								// Onto the next step
								activate_plugins( _this );
							}
						}
					} );
				} else {
					// Remove current plugin from queue
					delete plugins[index];

					if ( $.isEmptyObject( plugins ) ) {
						// Onto the next step
						activate_plugins( _this );
					}
				}

			} );
		} else {
			download_content( _this );
		}
	} );

	function activate_plugins( _this ) {
		_this.closest( '.site-box' ).find( '.site-message' ).text( generate_sites_params.activating_plugins ).show();
		var data = _this.closest( '.site-box' ).data( 'site-data' );

		setTimeout( function() {
			$.ajax( {
				type: 'POST',
				url: generate_sites_params.ajaxurl,
				data: {
					action: 'generate_activate_plugins_' + data.slug,
					nonce: generate_sites_params.nonce,
				},
				success: function( data ) {
					console.log( data );
					download_content( _this );
				},
				error: function( data ) {
					console.log( data );
					_this.closest( '.site-box' ).find( '.loading' ).hide();
					_this.closest( '.site-box' ).find( '.error-message' ).text( data.status + ' ' + data.statusText ).show();
				}
			} );
		}, 250 );
	}

	function download_content( _this ) {
		_this.closest( '.site-box' ).find( '.site-message' ).text( generate_sites_params.downloading_content ).show();
		var data = _this.closest( '.site-box' ).data( 'site-data' );

		$.ajax( {
			type: 'POST',
			url: generate_sites_params.ajaxurl,
			data: {
				action: 'generate_download_content_' + data.slug,
				nonce: generate_sites_params.nonce,
			},
			success: function( data ) {
				console.log( data );

				import_content( _this );
			},
			error: function( data ) {
				console.log( data );
				_this.closest( '.site-box' ).find( '.loading' ).hide();
				_this.closest( '.site-box' ).find( '.error-message' ).text( data.status + ' ' + data.statusText ).show();
			}
		} );
	}

	function import_content( _this ) {
		_this.closest( '.site-box' ).find( '.site-message' ).text( generate_sites_params.importing_content ).show();
		var data = _this.closest( '.site-box' ).data( 'site-data' );

		$.ajax( {
			type: 'POST',
			url: generate_sites_params.ajaxurl,
			data: {
				action: 'generate_import_content_' + data.slug,
				nonce: generate_sites_params.nonce,
			},
			success: function( data ) {
				console.log( data );

				import_site_options( _this );
			},
			error: function( data ) {
				console.log( data );
				_this.closest( '.site-box' ).find( '.loading' ).hide();
				_this.closest( '.site-box' ).find( '.error-message' ).text( data.status + ' ' + data.statusText ).show();
			}
		} );
	}

	/**
	 * Import site options.
	 * Comes last, as options may be dependent on plugins.
	 */
	function import_site_options( _this ) {
		_this.closest( '.site-box' ).find( '.site-message' ).text( generate_sites_params.importing_site_options ).show();
		var data = _this.closest( '.site-box' ).data( 'site-data' );

		setTimeout( function() {
			$.ajax( {
				type: 'POST',
				url: generate_sites_params.ajaxurl,
				data: {
					action: 'generate_import_site_options_' + data.slug,
					nonce: generate_sites_params.nonce,
				},
				success: function( data ) {
					console.log( data );

					if ( '1' == _this.data( 'widgets' ) ) {

						import_widgets( _this );

					} else {

						setTimeout( function() {
							_this.next( '.loading' ).hide();
							_this.closest( '.site-box' ).find( '.site-message' ).hide();
							_this.next( '.loading' ).next( '.complete' ).fadeIn();
						}, 250 );

						setTimeout( function() {
							_this.closest( '.steps' ).hide().next( '.steps' ).show();
						}, 1000 );

					}
				},
				error: function( data ) {
					console.log( data );
					_this.closest( '.site-box' ).find( '.loading' ).hide();
					_this.closest( '.site-box' ).find( '.error-message' ).text( data.status + ' ' + data.statusText ).show();
				}
			} );
		}, 250 );
	}

	/**
	 * Import widgets.
	 */
	 function import_widgets( _this ) {
		 _this.closest( '.site-box' ).find( '.site-message' ).text( generate_sites_params.importing_widgets ).show();
		 var data = _this.closest( '.site-box' ).data( 'site-data' );

		 setTimeout( function() {
 			$.ajax( {
 				type: 'POST',
 				url: generate_sites_params.ajaxurl,
 				data: {
 					action: 'generate_import_widgets_' + data.slug,
 					nonce: generate_sites_params.nonce,
 				},
 				success: function( data ) {
 					console.log( data );

 					setTimeout( function() {
 						_this.next( '.loading' ).hide();
 						_this.closest( '.site-box' ).find( '.site-message' ).hide();
 						_this.next( '.loading' ).next( '.complete' ).fadeIn();
 					}, 250 );

 					setTimeout( function() {
 						_this.closest( '.steps' ).hide().next( '.steps' ).show();
 					}, 1000 );
 				},
 				error: function( data ) {
 					console.log( data );
					_this.closest( '.site-box' ).find( '.loading' ).hide();
					_this.closest( '.site-box' ).find( '.error-message' ).text( data.status + ' ' + data.statusText ).show();
 				}
 			} );
 		}, 250 );
	 }
} );
