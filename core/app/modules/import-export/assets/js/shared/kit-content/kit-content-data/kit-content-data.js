const kitContentData = [
	{
		type: 'templates',
		data: {
			title: __( 'Templates', 'elementor' ),
			features: {
				open: [
					__( 'Saved Templates', 'elementor' ),
				],
				locked: [
					__( 'Headers', 'elementor' ),
					__( 'Footers', 'elementor' ),
					__( 'Archives', 'elementor' ),
					__( 'Single Posts', 'elementor' ),
					__( 'Single Pages', 'elementor' ),
					__( 'Search Results', 'elementor' ),
					__( '404 Error Page', 'elementor' ),
					__( 'Popups', 'elementor' ),
					__( 'Global widgets', 'elementor' ),
				],
				tooltip: __( 'To import or export these components, you’ll need Elementor Pro.', 'elementor' ),
			},
		},
	},
	{
		type: 'content',
		data: {
			title: __( 'Content', 'elementor' ),
			features: {
				open: [
					__( 'Elementor Pages', 'elementor' ),
					__( 'Landing Pages', 'elementor' ),
					__( 'Elementor Posts', 'elementor' ),
					__( 'WP Pages', 'elementor' ),
					__( 'WP Posts', 'elementor' ),
					__( 'Custom Post Type', 'elementor' ),
				],
			},
		},
	},
	{
		type: 'settings',
		data: {
			title: __( 'Site Settings', 'elementor' ),
			features: {
				open: [
					__( 'Global Colors', 'elementor' ),
					__( 'Global Fonts', 'elementor' ),
					__( 'Theme Style settings', 'elementor' ),
					__( 'Layout Settings', 'elementor' ),
					__( 'Lightbox Settings', 'elementor' ),
					__( 'Background Settings', 'elementor' ),
				],
			},
		},
	},
];

export default kitContentData;
