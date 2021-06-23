import DocumentHelper from '../helper';
import ElementsHelper from './helper';
import HistoryHelper from '../history/helper';
import * as commands from './commands/index.spec.js';

QUnit.module( 'Component: document/elements', ( hooks ) => {
	hooks.beforeEach( () => {
		ElementsHelper.empty();

		HistoryHelper.resetItems();
	} );

	DocumentHelper.testCommands( commands );

	// QUnit.test( 'ResizeColumn', ( assert ) => {
	// 	//assert.equal( 1, 1, 'Test was skipped.' );
	//
	// 	const newSize = 20,
	// 			eSection = DocumentHelper.createSection( 2 ),
	// 			eColumn1 = eSection.view.children.findByIndex( 0 ).getContainer(),
	// 			eColumn2 = eSection.view.children.findByIndex( 1 ).getContainer(),
	// 			column2NewSize = 100 - newSize;
	//
	// 		const doneSettings = assert.async();
	//
	// 		// Manual specific `_inline_size` since tests does not have real ui.
	// 		$e.run( 'document/elements/settings', {
	// 			containers: [ eColumn1, eColumn2 ],
	// 			settings: {
	// 				[ eColumn1.id ]: { _inline_size: 50 },
	// 				[ eColumn2.id ]: { _inline_size: 50 },
	// 			},
	// 			isMultiSettings: true,
	// 		} );
	//
	// 		setTimeout( () => doneSettings() );
	//
	// 		DocumentHelper.resizeColumn( eColumn1, newSize );
	//
	// 		const doneResize = assert.async();
	//
	// 		setTimeout( () => {
	// 			// Check values.
	// 			assert.equal( eColumn1.settings.attributes._inline_size, newSize,
	// 				`Column1 size was changed to '${ newSize }'.` );
	// 			assert.equal( eColumn2.settings.attributes._inline_size, ( column2NewSize ),
	// 				`Column2 size was changed to '${ column2NewSize }'.` );
	//
	// 			doneResize();
	// 		} );
	// } );
} );
