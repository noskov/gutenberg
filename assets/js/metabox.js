( function( iFrameResize, $ ) {
	$( document ).ready( function() {
		iFrameResize( { resizeFrom: 'child', heightCalculationMethod: 'documentElementScroll' }, '#gutenberg-metabox-iframe' );
	} );
} )( window.iFrameResize, jQuery );
