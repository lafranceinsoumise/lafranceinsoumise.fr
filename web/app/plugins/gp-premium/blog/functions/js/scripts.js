jQuery( document ).ready( function( $ ) {
	var $masonry_container = $( '.masonry-container' );
	var msnry = false;

	if ( $masonry_container.length ) {
		var $grid = $masonry_container.masonry( generateBlog.masonryInit );

		msnry = $grid.data( 'masonry' );

		$grid.imagesLoaded( function() {
			$grid.masonry( 'layout' );
			$grid.removeClass( 'are-images-unloaded' );
			$( '.load-more' ).removeClass( 'are-images-unloaded' );
			$( '#nav-below' ).css( 'opacity', '1' );
			$grid.masonry( 'option', { itemSelector: '.masonry-post' });
			var $items = $grid.find( '.masonry-post' );
			$grid.masonry( 'appended', $items );
		} );

		$( '#nav-below' ).insertAfter( '.masonry-container' );

		$( window ).on( "orientationchange", function( event ) {
			$grid.masonry( 'layout' );
		} );
	}

	if ( $( '.infinite-scroll' ).length && $( '.nav-links .next' ).length ) {
		var $container = $( '#main article' ).first().parent();
		var $button = $( '.load-more a' );
		var svgIcon = '';

		if ( generateBlog.icon ) {
			svgIcon = generateBlog.icon;
		}

		var infiniteScrollInit = generateBlog.infiniteScrollInit;

		infiniteScrollInit.outlayer = msnry;

		$container.infiniteScroll( infiniteScrollInit );

		$button.on( 'click', function( e ) {
			document.activeElement.blur();
			$( this ).html( svgIcon + generateBlog.loading ).addClass( 'loading' );
		} );

		$container.on( 'append.infiniteScroll', function( event, response, path, items ) {
			if ( ! $( '.generate-columns-container' ).length ) {
				$container.append( $button.parent() );
			}

			// Fix srcset images not loading in Safari.
			// img.outerHTML = img.outerHTML isn't minified properly, so we need the extra var.
			$( items ).find( 'img' ).each( function( index, img ) {
				var imgOuterHTML = img.outerHTML;
				img.outerHTML = imgOuterHTML;
			} );

			if ( $grid ) {
				$grid.imagesLoaded( function() {
					$grid.masonry( 'layout' );
				} );
			}

			$button.html( svgIcon + generateBlog.more ).removeClass( 'loading' );
			$( document.body ).trigger( 'post-load' );
		} );

		$container.on( 'last.infiniteScroll', function() {
			$( '.load-more' ).hide();
		} );
	}
} );
