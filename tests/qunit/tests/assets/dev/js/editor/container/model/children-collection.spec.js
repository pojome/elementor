import ElementsHelper from 'elementor/tests/qunit/tests/assets/dev/js/editor/document/elements/helper';

QUnit.module( 'File: assets/dev/js/editor/container/model/children-collection.js', ( hooks ) => {
	hooks.beforeEach( () => {
		ElementsHelper.empty();
	} );

	QUnit.test( 'clear(): simple', ( assert ) => {
		// Arrange.
		const eColumn = ElementsHelper.createSection( 1, 1 );

		ElementsHelper.createButton( eColumn );
		ElementsHelper.createButton( eColumn );

		assert.equal( eColumn.children.length, 2 );

		// Act.
		eColumn.children.clear();

		// Assert.
		assert.equal( eColumn.children.length, 0 );
	} );

	QUnit.test( 'findRecursive(): Ensure children found', ( assert ) => {
		// Arrange.
		const eColumn = ElementsHelper.createSection( 1, 1 ),
			eWidgets = [
				ElementsHelper.createButton( eColumn ),
				ElementsHelper.createButton( eColumn ),
			];

		eWidgets.forEach( ( eWidget ) => {
			// Act.
			const foundChildren = elementor.getPreviewContainer().children.findRecursive(
				( container ) => container.id === eWidget.id
			);

			// Assert.
			assert.equal( foundChildren, eWidget );
		} );
	} );

	QUnit.test( 'forEachRecursive(): Ensure works', ( assert ) => {
		// Arrange.
		const eSection = ElementsHelper.createSection( 1 ),
			eColumn = eSection.children[ 0 ],
			eWidgetsIds = [
				ElementsHelper.createButton( eColumn ).id,
				ElementsHelper.createButton( eColumn ).id,
			],
			expectedIds = [ eSection.id, eColumn.id, ... eWidgetsIds ],
			actualIds = [];

		// Act.
		elementor.getPreviewContainer().children.forEachRecursive( ( container ) => actualIds.push( container.id ) );

		// Assert.
		assert.deepEqual( actualIds, expectedIds );
	} );

	QUnit.test( 'someRecursive(): Ensure .some() stops at positive value', ( assert ) => {
		// Arrange.
		const eSection = ElementsHelper.createSection( 1 ),
			eColumn = eSection.children[ 0 ];

		ElementsHelper.createButton( eColumn );
		ElementsHelper.createButton( eColumn );

		let iterationCounter = 0;

		// Act.
		eSection.children.someRecursive( ( container ) => {
			++iterationCounter;
			if ( container.id === eColumn.id ) {
				return true;
			}
		} );

		// Assert.
		assert.equal( iterationCounter, 1 );
	} );

	QUnit.test( 'someRecursive(): Ensure nested', ( assert ) => {
		// Arrange.
		ElementsHelper.empty();
		ElementsHelper.createAutoButton();

		const eButtonInSection2 = ElementsHelper.createAutoButton();

		let iterationCounter = 0;

		// Act.
		elementor.getPreviewContainer().children.someRecursive( ( container ) => {
			if ( container.id === eButtonInSection2.id ) {
				return true;
			}
			++iterationCounter;
		} );

		// Assert.
		assert.equal( iterationCounter, 5 );
	} );
} );

