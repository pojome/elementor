import tests from '../tests/frontend';

export default class FrontendBootstrap {
	constructor() {
		jQuery( this.initialize.bind( this ) );
	}

	initialize() {
		const FrontendTest = require( './test' ).default,
			$body = jQuery( 'body' ).append( '<div id="elementor-test"></div>' ),
			$elementorTest = $body.find( '#elementor-test' );

		// Load the template to `#elementor-test`.
		$elementorTest.append( window.__html__[ 'tests/qunit/setup/frontend/index.html' ] );

		window.elementorFrontend = new FrontendTest();

		elementorFrontend.on( 'components:init', () => {
			this.runTests();
		} );

		elementorFrontend.init();
	}

	runTests() {
		return tests();
	}
}
