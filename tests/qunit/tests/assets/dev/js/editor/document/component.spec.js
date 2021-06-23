import DocumentHelper from './helper';
import ElementsHelper from './elements/helper';
import * as hooksData from './hooks/data/document/elements/index.spec';
import * as eData from 'elementor/tests/qunit/mock/e-data';

QUnit.module( 'Component: document', ( rootHooks ) => {
	QUnit.test( 'History per document', ( assert ) => {
		eData.attachCache();

		// Save current document before go.
		const documentBeforeTest = elementor.documents.getCurrent(),
			documentConfigMaster = { id: 2 },
			documentConfigSlave = { id: 3 };

		// Add fake documents.
		const documentMaster = elementor.documents.addDocumentByConfig( documentConfigMaster ),
			documentSlave = elementor.documents.addDocumentByConfig( documentConfigSlave );

		documentMaster.container = documentBeforeTest.container;
		documentSlave.container = documentBeforeTest.container;

		// Set current document to master.
		elementor.documents.setCurrent( documentMaster );

		// Create button and save it under master document.
		const eButton = ElementsHelper.createAutoButton();

		// Validate the button was saved to history of master document.
		assert.equal( elementor.documents.getCurrent().history.getItems().length, 3,
			'Master document have "3" items in history.' );

		// Set current document to slave.
		elementor.documents.setCurrent( documentSlave );

		// Validate document does not have History.
		assert.equal( elementor.documents.getCurrent().history.getItems().length, 0,
			'Slave document does not have items in history.' );

		// Do change under slave document.
		ElementsHelper.settings( eButton, {
			text: 'Some other value',
		} );

		const done = assert.async(); // Pause the test till done.

		setTimeout( () => {
			// Validate history of slave document was affected.
			assert.equal( elementor.documents.getCurrent().history.getItems().length, 2,
				'Slave document have "2" items in history.' );

			// Ensure history under slave document have button settings changed.
			assert.equal( elementor.documents.getCurrent().history.getItems().at( 0 ).attributes.type, 'change',
				'Slave document was affected and there is "change" type in it.' );

			// Set current document to master.
			elementor.documents.setCurrent( documentMaster );

			// Ensure master still have only initial history.
			assert.equal( elementor.documents.getCurrent().history.getItems().length, 3,
				'Master document still have "3" items in history.' );

			// Put back saved document, to current.
			elementor.documents.setCurrent( documentBeforeTest );
			eData.emptyFetch();

			done();
		} );
	} );

	QUnit.module( `Hooks`, ( hooks ) => {
		hooks.beforeEach( () => {
			ElementsHelper.empty();
		} );

		Object.entries( hooksData ).forEach( ( [ hookNamespace, hook ] ) => {
			QUnit.module( hookNamespace, () => {
				DocumentHelper.testCommands( hook );
			} );
		} );
	} );

	require( './elements/component.spec' );
	require( './globals/component.spec' );
	require( './repeater/component.spec' );
	require( './dynamic/component.spec' );
	require( './history/component.spec' );
	require( './ui/component.spec' );
	require( './save/component.spec' );
} );

