import DocumentHelper from 'elementor-tests-qunit/tests/assets/dev/js/editor/document/helper';
import ElementsHelper from 'elementor-tests-qunit/tests/assets/dev/js/editor/document/elements/helper';

import * as commands from './commands/index.spec';

jQuery( () => {
	QUnit.module( 'Component: navigator', ( hooks ) => {
		hooks.before( () => {
			ElementsHelper.empty();

			// Since UI hooks are disabled while testing, and init of navigator required for this tests.
			$e.hooks.ui.get( 'navigator-initialize--editor/documents/attach-preview' ).callback( {} );
		} );

		// After handling all test(s), keep it close.
		hooks.after( () => {
			$e.run( 'navigator/close' );

			elementor.navigator.getLayout().destroy();
		} );

		DocumentHelper.testCommands( commands );

		require( './elements/component.spec' );
	} );
} );
