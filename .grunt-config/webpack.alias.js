const path = require( 'path' );

module.exports = {
	resolve: {
		alias: {
			'elementor': path.resolve( __dirname, '../' ),
			'elementor-app': path.resolve( __dirname, '../core/app/assets/js' ),
			'elementor-admin': path.resolve( __dirname, '../assets/dev/js/admin' ),
			'elementor-api': path.resolve( __dirname, '../core/common/assets/js/api' ),
			'elementor-assets-js': path.resolve( __dirname, '../assets/dev/js' ),
			'elementor-behaviors': path.resolve( __dirname, '../assets/dev/js/editor/elements/views/behaviors' ),
			'elementor-common': path.resolve( __dirname, '../core/common/assets/js' ),
			'elementor-common-modules': path.resolve( __dirname, '../core/common/modules' ),
			'elementor-controls': path.resolve( __dirname, '../assets/dev/js/editor/controls' ),
			'elementor-document': path.resolve( __dirname, '../assets/dev/js/editor/document' ),
			'elementor-dynamic-tags': path.resolve( __dirname, '../assets/dev/js/editor/components/dynamic-tags' ),
			'elementor-editor': path.resolve( __dirname, '../assets/dev/js/editor' ),
			'elementor-editor-utils': path.resolve( __dirname, '../assets/dev/js/editor/utils' ),
			'elementor-elements': path.resolve( __dirname, '../assets/dev/js/editor/elements' ),
			'elementor-frontend': path.resolve( __dirname, '../assets/dev/js/frontend' ),
			'elementor-panel': path.resolve( __dirname, '../assets/dev/js/editor/regions/panel' ),
			'elementor-regions': path.resolve( __dirname, '../assets/dev/js/editor/regions' ),
			'elementor-revisions': path.resolve( __dirname, '../assets/dev/js/editor/components/revisions' ),
			'elementor-scss': path.resolve( __dirname, '../assets/dev/scss' ),
			'elementor-templates': path.resolve( __dirname, '../assets/dev/js/editor/components/template-library' ),
			'elementor-utils': path.resolve( __dirname, '../assets/dev/js/utils' ),
			'elementor-validator': path.resolve( __dirname, '../assets/dev/js/editor/components/validator' ),
			'elementor-views': path.resolve( __dirname, '../assets/dev/js/editor/views' ),
			'elementor-tests-qunit': path.resolve( __dirname, '../tests/qunit' ),
		},
	},
};
