const { addFilter } = wp.hooks;
const { createHigherOrderComponent } = wp.compose;
const { createElement, Fragment } = wp.element;
const { InspectorControls } = wp.blockEditor || wp.editor;
const { PanelBody, SelectControl } = wp.components;

const addQueryPostStatusAttribute = ( settings, name ) => {
	if ( name !== 'core/query' ) {
		return settings;
	}

	return {
		...settings,
		attributes: {
			...settings.attributes,
			smnPostStatus: {
				type: 'string',
				default: '',
			},
		},
	};
};

addFilter(
	'blocks.registerBlockType',
	'smn/query-post-status-attribute',
	addQueryPostStatusAttribute
);

const withQueryPostStatusControl = createHigherOrderComponent( ( BlockEdit ) => {
	return ( props ) => {
		if ( props.name !== 'core/query' ) {
			return createElement( BlockEdit, props );
		}

		const value = props.attributes.smnPostStatus || 'publish';

		return createElement(
			Fragment,
			null,
			createElement( BlockEdit, props ),
			createElement(
				InspectorControls,
				null,
				createElement(
					PanelBody,
					{ title: 'Estado de publicación', initialOpen: false },
					createElement( SelectControl, {
						label: 'Post status',
						value,
						options: [
							{ label: 'Publicado', value: 'publish' },
							{ label: 'Borrador', value: 'draft' },
							{ label: 'Pendiente', value: 'pending' },
							{ label: 'Programado', value: 'future' },
							{ label: 'Privado', value: 'private' },
							{ label: 'Cualquiera', value: 'any' },
						],
						onChange: ( nextValue ) => {
							const nextQuery = {
								...( props.attributes.query || {} ),
							};

							nextQuery.status = nextValue;

							if ( nextValue !== 'publish' ) {
								nextQuery.inherit = false;
							}

							props.setAttributes( {
								smnPostStatus: nextValue,
								query: nextQuery,
							} );
						},
					} )
				)
			)
		);
	};
}, 'withQueryPostStatusControl' );

addFilter(
	'editor.BlockEdit',
	'smn/query-post-status-control',
	withQueryPostStatusControl
);
