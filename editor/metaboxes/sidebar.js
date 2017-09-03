/**
 * External dependencies
 */
import classnames from 'classnames';
import { connect } from 'react-redux';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';

/**
 * Internal dependencies
 */
import './style.scss';
import { setMetaboxReference } from '../actions';

class SidebarMetaboxes extends Component {
	constructor() {
		super();

		this.state = {
			cool: 'yeah',
		};
	}

	componentDidMount() {
		// Sets a React Node Reference into the store.
		this.props.setReference( this.props.location, this.node );
	}

	render() {
		const { location, id = 'gutenberg-metabox-iframe' } = this.props;
		const classes = classnames( {
			'gutenberg-metabox-iframe': true,
		} );

		return (
			<iframe
				ref={ ( node ) => {
					this.node = node;
				} }
				title={ __( 'Extended Settings' ) }
				key="metabox"
				id={ id }
				className={ classes }
				src={ `${ window._wpMetaboxUrl }&metabox=${ location }` } />
		);
	}
}

function mapDispatchToProps( dispatch ) {
	return {
		// Used to set the reference to the Metabox in redux, fired when the component mounts.
		setReference: ( location, node ) => dispatch( setMetaboxReference( location, node ) ),
	};
}

export default connect( null, mapDispatchToProps )( SidebarMetaboxes );
