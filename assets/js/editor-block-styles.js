wp.domReady( () => {
	const { registerBlockStyle, registerBlockVariation } = wp.blocks;

	const safeRegisterStyle = ( blockName, style ) => {
		try {
			registerBlockStyle( blockName, style );
		} catch ( e ) {
			// Prevent one duplicate style from blocking the rest.
		}
	};

	safeRegisterStyle( 'core/list', { name: 'separator-list', label: 'Separador' } );
	safeRegisterStyle( 'core/list', { name: 'arrow-list', label: 'Flecha simple' } );
	safeRegisterStyle( 'core/list', { name: 'arrow-separator-list', label: 'Flecha con separador' } );
	safeRegisterStyle( 'core/list', { name: 'arrow-mini-separator-list', label: 'Flecha mini con separador' } );
	safeRegisterStyle( 'core/list', { name: 'check-separator-list', label: 'Check con separador' } );
	safeRegisterStyle( 'core/list', { name: 'check-list', label: 'Check simple' } );

	safeRegisterStyle( 'core/button', { name: 'with-arrow', label: 'Con Flecha' } );
	safeRegisterStyle( 'core/button', { name: 'outline-with-arrow', label: 'Contorno con Flecha' } );

	safeRegisterStyle( 'core/paragraph', { name: 'title-has-image', label: 'Imagen integrada' } );
	safeRegisterStyle( 'core/paragraph', { name: 'eyebrow', label: 'Ceja' } );
	safeRegisterStyle( 'core/heading', { name: 'eyebrow', label: 'Ceja' } );
	safeRegisterStyle( 'core/paragraph', { name: 'rounded-eyebrow', label: 'Ceja Redondeada' } );
	safeRegisterStyle( 'core/heading', { name: 'rounded-eyebrow', label: 'Ceja Redondeada' } );
	safeRegisterStyle( 'core/paragraph', { name: 'center-on-mobile', label: 'Center on Mobile' } );
	safeRegisterStyle( 'core/heading', { name: 'center-on-mobile', label: 'Center on Mobile' } );
	safeRegisterStyle( 'core/buttons', { name: 'center-on-mobile', label: 'Center on Mobile' } );
	safeRegisterStyle( 'core/group', { name: 'center-on-mobile', label: 'Center on Mobile' } );
	safeRegisterStyle( 'core/cover', { name: 'cover-contain-background', label: 'Contain Background' } );
	safeRegisterStyle( 'core/quote', { name: 'quote-card', label: 'Card' } );
	safeRegisterStyle( 'core/query', { name: 'is-related-posts', label: 'Related by category' } );
	safeRegisterStyle( 'core/group', { name: 'group-horizontal-scroll', label: 'Horizontal scroll' } );
	safeRegisterStyle( 'core/group', { name: 'group-horizontal-scroll-btns', label: 'Horizontal scroll with buttons' } );
	safeRegisterStyle( 'core/group', { name: 'margin-vertical', label: 'Margin vertical (top & bottom 80px)' } );

	try {
		registerBlockVariation( 'core/group', {
			name: 'group-horizontal-scroll',
			title: 'Horizontal scroll group',
			icon: 'leftright',
			description: 'Items in Horizontal scroll',
			isDefault: false,
			attributes: {
				className: 'is-style-group-horizontal-scroll',
			},
		} );
	} catch ( e ) {
		// Ignore duplicate variation registration.
	}

	try {
		registerBlockVariation( 'core/group', {
			name: 'group-horizontal-scroll-btns',
			title: 'Horizontal scroll group with buttons',
			icon: 'sort',
			description: 'Items in Horizontal scroll with buttons',
			isDefault: false,
			attributes: {
				className: 'is-style-group-horizontal-scroll--btns',
			},
		} );
	} catch ( e ) {
		// Ignore duplicate variation registration.
	}
} );
