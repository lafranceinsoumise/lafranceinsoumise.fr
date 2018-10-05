jQuery( document ).ready( function( $ ) {
	$( '.wc-has-gallery .wc-product-image' ).hover(
		function() {
			$( this ).find( '.secondary-image' ).css( 'opacity','1' );
		}, function() {
			$( this ).find( '.secondary-image' ).css( 'opacity','0' );
		}
	);
});