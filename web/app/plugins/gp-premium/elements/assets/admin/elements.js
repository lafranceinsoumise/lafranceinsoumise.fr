jQuery( document ).ready( function( $ ) {
	$( '.post-type-gp_elements .page-title-action:not(.legacy-button)' ).on( 'click', function( e ) {
		e.preventDefault();

		$( '.choose-element-type-parent' ).show();
	} );

	$( '.close-choose-element-type' ).on( 'click', function( e ) {
		e.preventDefault();

		$( '.choose-element-type-parent' ).hide();
	} );
} );
