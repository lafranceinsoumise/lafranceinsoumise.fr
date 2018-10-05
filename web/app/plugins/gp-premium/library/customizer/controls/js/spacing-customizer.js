( function( $, api ) {
	// No longer needed as of 1.2.95
	// Keeping it here just in case
	api.controlConstructor['spacing'] = api.Control.extend( {
		ready: function() {
			var control = this;
			$( '.generate-number-control', control.container ).on( 'change keyup',
				function() {
					control.setting.set( $( this ).val() );
				}
			);
		}
	} );
	
	api.controlConstructor['gp-spacing-slider'] = api.Control.extend( {
		ready: function() {
			var control = this;
			$( '.slider-input', control.container ).on( 'change keyup',
				function() {
					control.setting.set( $( this ).val() );
				}
			);
		}
	} );
	
	api.controlConstructor['generatepress-spacing'] = api.Control.extend( {
		ready: function() {
			var control = this;

			control.container.on( 'change keyup', '.spacing-top',
				function() {
					control.settings['top'].set( jQuery( this ).val() );
				}
			);

			control.container.on( 'change keyup', '.spacing-right',
				function() {
					control.settings['right'].set( jQuery( this ).val() );
				}
			);

			control.container.on( 'change keyup', '.spacing-bottom',
				function() {
					control.settings['bottom'].set( jQuery( this ).val() );
				}
			);

			control.container.on( 'change keyup', '.spacing-left',
				function() {
					control.settings['left'].set( jQuery( this ).val() );
				}
			);
		}
	} );
} )( jQuery, wp.customize );

jQuery( document ).ready( function($) {
	$( '.gp-link-spacing' ).on( 'click', function(e) {
		e.preventDefault();
		
		// Set up variables
		var _this = $( this ),
		element = _this.data( 'element' );
		
		// Add our linked-values class to the next 4 elements
		_this.parent( '.gp-spacing-section' ).prevAll().slice(0,4).find( 'input' ).addClass( 'linked-values' ).attr( 'data-element', element );
		
		// Change our link icon class
		_this.hide();
		_this.next( 'span' ).show();
	});
	
	$( '.gp-unlink-spacing' ).on( 'click', function(e) {
		e.preventDefault();
		
		// Set up variables
		var _this = $( this );
		
		// Remove our linked-values class to the next 4 elements
		_this.parent( '.gp-spacing-section' ).prevAll().slice(0,4).find( 'input' ).removeClass( 'linked-values' ).attr( 'data-element', '' );
		
		// Change our link icon class
		_this.hide();
		_this.prev( 'span' ).show();
	});
	
	$( '.gp-spacing-section' ).on( 'input', '.linked-values', function() {
		var data = $( this ).attr( 'data-element' );
		var val = $( this ).val();
		$( '.linked-values[ data-element="' + data + '" ]' ).each( function( key, value ) {
			var element = $( this );
			element.val( val ).change();
		});
	});
});