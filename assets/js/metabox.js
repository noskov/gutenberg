( function( iFrameResize, $ ) {
	$( document ).ready( function() {
		iFrameResize( { resizeFrom: 'child', heightCalculationMethod: 'documentElementScroll' }, '#gutenberg-metabox-iframe' );
		iFrameResize( { resizeFrom: 'child', heightCalculationMethod: 'documentElementScroll' }, '#gutenberg-metabox-iframe-sidebar' );
	} );
} )( window.iFrameResize, jQuery );
