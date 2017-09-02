/**
 * Internal dependencies
 */
import {
	focusBlock,
	replaceBlocks,
	startTyping,
	stopTyping,
	updateMetaboxes,
	setMetaboxReference,
} from '../actions';

describe( 'actions', () => {
	describe( 'focusBlock', () => {
		it( 'should return the UPDATE_FOCUS action', () => {
			const focusConfig = {
				editable: 'cite',
			};

			expect( focusBlock( 'chicken', focusConfig ) ).toEqual( {
				type: 'UPDATE_FOCUS',
				uid: 'chicken',
				config: focusConfig,
			} );
		} );
	} );

	describe( 'replaceBlocks', () => {
		it( 'should return the REPLACE_BLOCKS action', () => {
			const blocks = [ {
				uid: 'ribs',
			} ];

			expect( replaceBlocks( [ 'chicken' ], blocks ) ).toEqual( {
				type: 'REPLACE_BLOCKS',
				uids: [ 'chicken' ],
				blocks,
			} );
		} );
	} );

	describe( 'startTyping', () => {
		it( 'should return the START_TYPING action', () => {
			expect( startTyping() ).toEqual( {
				type: 'START_TYPING',
			} );
		} );
	} );

	describe( 'stopTyping', () => {
		it( 'should return the STOP_TYPING action', () => {
			expect( stopTyping() ).toEqual( {
				type: 'STOP_TYPING',
			} );
		} );
	} );

	describe( 'updateMetaboxes', () => {
		it( 'should return the UPDATE_METABOXES action', () => {
			expect( updateMetaboxes() ).toEqual( {
				type: 'UPDATE_METABOXES',
			} );
		} );
	} );

	describe( 'setMetaboxReference', () => {
		it( 'should return the SET_METABOX_REFERENCE action with a location and node', () => {
			const location = 'side';
			const node = { i: 'is node' };
			expect( setMetaboxReference( location, node ) ).toEqual( {
				type: 'SET_METABOX_REFERENCE',
				data: {
					location,
					node,
				},
			} );
		} );
	} );
} );
