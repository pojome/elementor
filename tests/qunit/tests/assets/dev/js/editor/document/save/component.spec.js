import DocumentHelper from '../helper';
import ElementsHelper from '../elements/helper';
import * as commands from './commands/index.spec.js';
import * as commandsInternal from './commands/internal/index.spec.js';
import * as Ajax from 'elementor/tests/qunit/mock/ajax';

QUnit.module( 'Component: document/save', ( hooks ) => {
	hooks.before( () => {
		// Hook `elementorCommon.ajax.send` mock.
		Ajax.mock();
	} );

	hooks.after( () => {
		// Hook `elementorCommon.ajax.send` silence (empty function).
		Ajax.silence();
	} );

	DocumentHelper.testCommands( commands );
	DocumentHelper.testCommands( commandsInternal );

	QUnit.test( 'Auto save timer', ( assert ) => {
		const saveComponent = $e.components.get( 'document/save' ),
			defaultAutoSaveInterval = saveComponent.autoSaveInterval;

		// Make it quick.
		saveComponent.autoSaveInterval = 0;

		// Stimulate and start auto save timer.
		ElementsHelper.createSection( 1 );

		const done = assert.async( 2 ),
			callback = ( component, command, args ) => {
				if ( 'document/save/save' === command ) {
					$e.commandsInternal.off( 'run:before', callback );

					args.onAfter = async ( _args, _results ) => {
						const saveResult = await _results;
						assert.equal( saveResult.data.status, 'inherit' );
						assert.equal( elementor.documents.getCurrent().editor.isSaved, true );

						// Restore defaults.
						saveComponent.autoSaveInterval = defaultAutoSaveInterval;

						// Resume.
						done();
					};
				}
			};

		/**
		 * Flow: Create section will trigger 'document/save/set-is-modified' that will trigger
		 * autosave timer, on timer timeout it will call 'document/save/save' which will create a promise.
		 * on promise resolve, it will eventually set 'elementor.documents.getCurrent().editor.isSaved = true'.
		 * Explanation: on after save, we catch the promise via '_results', then await for promise resolve.
		 */
		$e.commandsInternal.on( 'run:before', callback );
	} );
} );

