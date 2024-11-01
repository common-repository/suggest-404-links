wp.blocks.registerBlockType( 'tekod/suggest404links', {
	
	title: wp.i18n.__( '404 page: similar links', 'suggest-404-links' ),
	icon: 'admin-links',
	category: 'widgets',
	attributes:  {},
	supports: {
		color: true
	},
	
	edit( props ) {
		const blockProps = wp.blockEditor.useBlockProps();
		return wp.element.createElement('div', {className:'suggest_404_links'}, [
			wp.element.createElement( 'blockquote', blockProps, wp.i18n.__( 'List of links similar to current 404 page', 'suggest-404-links' ) ),
		] )
	},
	
});