import DocumentHelper from 'elementor/tests/qunit/tests/assets/dev/js/editor/document/helper';
import * as commands from './commands/index.spec';

QUnit.module( 'Component: globals/colors', () => {
	DocumentHelper.testCommands( commands );
} );
