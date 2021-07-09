import * as ajax from 'elementor/tests/qunit/mock/ajax';
import * as eData from 'elementor/tests/qunit/mock/e-data';
import * as hooks from './hooks/index.spec';
import DocumentHelper from 'elementor/tests/qunit/tests/assets/dev/js/editor/document/helper';

QUnit.module( 'Component: panel/global', () => {
	QUnit.module( 'hooks', ( qunitHooks ) => {
		qunitHooks.before( async () => {
			// Hook `elementorCommon.ajax.send` mock.
			ajax.mock();

			// Default addMock callback is return args.data merged with args.query.
			eData.addMock( 'get', 'globals/index' );
			eData.attachMock();

			await $e.run( 'panel/global/open' );
			ajax.silence();
		} );

		qunitHooks.after( async () => {
			ajax.mock();
			await $e.run( 'panel/global/close', { mode: 'discard' } );
			ajax.silence();

			// Hook `elementorCommon.ajax.send` silence (empty function).
			eData.emptyFetch();

			eData.removeMock( 'get', 'globals/index' );
		} );

		DocumentHelper.testCommands( hooks );
	} );
} );
