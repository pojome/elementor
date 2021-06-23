import ElementsHelper from '../elements/helper';
import HistoryHelper from './helper';

QUnit.module( 'Component: document/history', ( hooks ) => {
	hooks.beforeEach( () => {
		ElementsHelper.empty();

		HistoryHelper.resetItems();
	} );

	// QUnit.test( 'Resize Column', ( assert ) => {
	// 	assert.equal( 1, 1, 'Test skipped.' );
	// 	/*const newSize = 20,
	// 		eSection = Elements.createSection( 2 ),
	// 		eColumn1 = eSection.view.children.findByIndex( 0 ).getContainer(),
	// 		eColumn2 = eSection.view.children.findByIndex( 1 ).getContainer();
	//
	// 	// Manual specific `_inline_size` since tests does not have real ui.
	// 	$e.run( 'document/elements/settings', {
	// 		containers: [ eColumn1, eColumn2 ],
	// 		settings: {
	// 			[ eColumn1.id ]: { _inline_size: 50 },
	// 			[ eColumn2.id ]: { _inline_size: 50 },
	// 		},
	// 		isMultiSettings: true,
	// 	} );
	//
	// 	Elements.resizeColumn( eColumn1, newSize );
	//
	// 	const historyItem = HistoryHelper.getFirstItem().attributes;
	//
	// 	// Exist in history.
	// 	inHistoryValidate( assert, historyItem, 'change', 'Column' );
	//
	// 	// Undo.
	// 	undoValidate( assert, historyItem );
	//
	// 	assert.equal( eColumn1.settings.attributes._inline_size, 50, 'Column1 back to default' );
	// 	assert.equal( eColumn2.settings.attributes._inline_size, 50, 'Column2 back to default' );
	//
	// 	// Redo.
	// 	redoValidate( assert, historyItem );
	//
	// 	assert.equal( eColumn1.settings.attributes._inline_size, newSize,
	// 		'Column1 restored' );
	// 	assert.equal( eColumn2.settings.attributes._inline_size, 100 - newSize,
	// 		'Column2 restored' );*/
	// } );

	// QUnit.test( 'Dynamic in repeater', ( assert ) => {
	// 	const eForm = DocumentHelper.createAutoForm(),
	// 		eFormItem = eForm.children[ 0 ],
	// 		dynamicTag = '[elementor-tag id="d96ebd2" name="post-date" settings="%7B%22format%22%3A%22d%2Fm%2FY%22%7D"]', // post-date with non default format.
	// 		dynamicValue = '{ dynamic text }',
	// 		{ id, name, settings } = elementor.dynamicTags.tagTextToTagData( dynamicTag ),
	// 		tag = elementor.dynamicTags.createTag( id, name, settings ),
	// 		key = elementor.dynamicTags.createCacheKey( tag );
	//
	// 	// Set fake data.
	// 	elementor.dynamicTags.cache[ key ] = dynamicValue;
	//
	// 	const doneAttach = assert.async();
	//
	// 	eFormItem.view.attachElContent = function( html ) {
	// 		debugger;
	// 		eFormItem.view.$el.empty().append( html );
	//
	// 		doneAttach();
	// 	};
	//
	// 	const done = assert.async();
	//
	// 	$e.run( 'document/dynamic/settings', {
	// 		container: eFormItem,
	// 		settings: { field_value: dynamicTag },
	// 	} );
	//
	// 	setTimeout( () => {
	// 		assert.equal( eForm.view.$el.find( '.button-text' ).html(), dynamicValue,
	// 			`button text changed to dynamic value: '${ dynamicValue }'` );
	//
	// 		done();
	// 	} )
	// } );

	// TODO: Temp next tests are not in the right place.
	QUnit.test( 'Saver Editor Flag', ( assert ) => {
		$e.internal( 'document/save/set-is-modified', { status: false } );

		ElementsHelper.createSection( 1 );

		const historyItem = HistoryHelper.getFirstItem().attributes;

		// Saver editor flag is `true`.
		assert.equal( elementor.saver.isEditorChanged(), true,
			'After create, saver editor flag is "true".' );

		// Undo.
		HistoryHelper.undoValidate( assert, historyItem );

		// Saver editor flag is `true`.
		assert.equal( elementor.saver.isEditorChanged(), false,
			'After create, saver editor flag is "false".' );

		// Redo.
		HistoryHelper.redoValidate( assert, historyItem );

		// Saver editor flag is `true`.
		assert.equal( elementor.saver.isEditorChanged(), true,
			'After create, saver editor flag is "true".' );
	} );

	QUnit.test( 'History Rollback', ( assert ) => {
		try {
			$e.run( 'document/elements/create', {
				container: ( new elementorModules.editor.Container( {} ) ),
				settings: {},
			} );
		} catch ( e ) {
			// Do nothing (ignore).
		}

		const historyItem = HistoryHelper.getFirstItem();

		assert.equal( historyItem, undefined, 'History was rolled back.' );
	} );
} );

