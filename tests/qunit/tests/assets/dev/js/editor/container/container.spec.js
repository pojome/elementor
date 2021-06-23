import ElementsHelper from 'elementor/tests/qunit/tests/assets/dev/js/editor/document/elements/helper';

jQuery( () => {
	QUnit.module( 'File: assets/dev/js/editor/container/container.js', () => {
		QUnit.test( 'constructor()', ( assert ) => {
			const fakeArgs = {
					type: 'fake',
					id: 'fake',
					settings: new Backbone.Model(),
					model: new Backbone.Model(),
					label: 'Fake Label',
				},
				container = new elementorModules.editor.Container( fakeArgs );

			assert.equal( !! container, true, );
		} );

		QUnit.test( 'constructor(): without args', ( assert ) => {
			assert.throws(
				() => {
					new elementorModules.editor.Container( {} );
				},
				new Error( 'type is required.' ),
			);
		} );

		QUnit.test( 'getGroupRelatedControls(): simple', ( assert ) => {
			const excepted = [ 'typography_typography', 'typography_font_family', 'typography_font_size', 'typography_font_size_tablet', 'typography_font_size_mobile', 'typography_font_weight', 'typography_text_transform', 'typography_font_style', 'typography_text_decoration', 'typography_line_height', 'typography_line_height_tablet', 'typography_line_height_mobile', 'typography_letter_spacing', 'typography_letter_spacing_tablet', 'typography_letter_spacing_mobile', 'button_text_color' ],
				settings = {
					typography_typography: '',
					button_text_color: '',
				},
				eButton = ElementsHelper.createAutoButton(),
				controls = eButton.getGroupRelatedControls( settings );

			assert.deepEqual( Object.keys( controls ), excepted );
		} );

		QUnit.test( 'findChildrenRecursive(): Ensure children found', ( assert ) => {
			// Arrange.
			const eColumn = ElementsHelper.createSection( 1, 1 ),
				eWidgets = [
					ElementsHelper.createButton( eColumn ),
					ElementsHelper.createButton( eColumn ),
				];

			eWidgets.forEach( ( eWidget ) => {
				// Act.
				const foundChildren = elementor.getPreviewContainer().findChildrenRecursive(
					( container ) => container.id === eWidget.id
				);

				// Assert.
				assert.equal( foundChildren, eWidget );
			} );
		} );

		QUnit.test( 'forEachChildrenRecursive(): Ensure works', ( assert ) => {
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
			eSection.forEachChildrenRecursive( ( container ) => actualIds.push( container.id ) );

			// Assert.
			assert.deepEqual( actualIds, expectedIds );
		} );
	} );
} );

