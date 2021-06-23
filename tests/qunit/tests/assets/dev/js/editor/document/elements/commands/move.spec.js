import ElementsHelper from '../helper';
import HistoryHelper from 'elementor/tests/qunit/tests/assets/dev/js/editor/document/history/helper';

export const Move = () => {
	QUnit.module( 'Move', () => {
		QUnit.module( 'Single Selection', () => {
			QUnit.test( 'Section', ( assert ) => {
				// Create Section at 0.
				ElementsHelper.createSection();

				const eSection = ElementsHelper.createSection( 3 );

				ElementsHelper.move( eSection, elementor.getPreviewContainer(), { at: 0 } );

				const done = assert.async();

				// Validate first section have 3 columns.
				setTimeout( () => {
					assert.equal( elementor.getPreviewContainer().children[ 0 ].children.length, 3,
						'Section were moved.' );

					done();
				} );
			} );

			QUnit.test( 'Column', ( assert ) => {
				const eSection1 = ElementsHelper.createSection(),
					eSection2 = ElementsHelper.createSection(),
					eColumn = ElementsHelper.createColumn( eSection1 );

				ElementsHelper.move( eColumn, eSection2 );

				// Validate.
				assert.equal( eSection2.children.length, 2,
					'Columns were moved.' );
			} );

			QUnit.test( 'Widget', ( assert ) => {
				const eSection = ElementsHelper.createSection(),
					eColumn1 = ElementsHelper.createColumn( eSection ),
					eColumn2 = ElementsHelper.createColumn( eSection ),
					eButton = ElementsHelper.createButton( eColumn1 );

				ElementsHelper.move( eButton, eColumn2 );

				// Validate.
				assert.equal( eColumn1.children.length, 0,
					'Widget were removed from first column.' );
				assert.equal( eColumn2.children.length, 1,
					'Widget were moved/created at the second column.' );
			} );

			QUnit.module( 'History', () => {
				QUnit.test( 'Section', ( assert ) => {
					// Create Section at 0.
					ElementsHelper.createSection();

					const eSection = ElementsHelper.createSection( 3 ),
						originalPosition = eSection.view._index,
						targetPosition = 0;

					ElementsHelper.move( eSection, elementor.getPreviewContainer(), { at: targetPosition } );

					const historyItem = HistoryHelper.getFirstItem().attributes;

					// Exist in history.
					HistoryHelper.inHistoryValidate( assert, historyItem, 'move', 'Section' );

					// Undo.
					HistoryHelper.undoValidate( assert, historyItem );

					const eSectionAfterUndo = eSection.lookup();

					assert.equal( eSectionAfterUndo.view._index, originalPosition,
						'Element has been returned to the original position' );

					// Redo.
					HistoryHelper.redoValidate( assert, historyItem );

					const eSectionAfterRedo = eSection.lookup();

					assert.equal( eSectionAfterRedo.view._index, targetPosition,
						'Element was re-added to correct position' );
				} );

				QUnit.test( 'Column between sections', ( assert ) => {
					const eSection1 = ElementsHelper.createSection(),
						eSection2 = ElementsHelper.createSection(),
						eColumn = ElementsHelper.createColumn( eSection1 ),
						originalPosition = eColumn.view._index,
						targetPosition = 1;

					ElementsHelper.move( eColumn, eSection2, { at: targetPosition } );

					const historyItem = HistoryHelper.getFirstItem().attributes;

					// Exist in history.
					HistoryHelper.inHistoryValidate( assert, historyItem, 'move', 'Column' );

					// Undo.
					HistoryHelper.undoValidate( assert, historyItem );

					const eColumnAfterUndo = eColumn.lookup();

					assert.equal( eColumnAfterUndo.view._index, originalPosition,
						'Element has been returned to the original position' );

					// Redo.
					HistoryHelper.redoValidate( assert, historyItem );

					const eColumnAfterRedo = eColumn.lookup();

					assert.equal( eColumnAfterRedo.view._index, targetPosition,
						'Element was re-added to correct position' );
				} );

				QUnit.test( 'Column in same section', ( assert ) => {
					const eSection = ElementsHelper.createSection();

					/* eColumn1 = */ ElementsHelper.createColumn( eSection );

					const eColumn2 = ElementsHelper.createColumn( eSection ),
						originalPosition = eColumn2.view._index,
						targetPosition = 0;

					ElementsHelper.move( eColumn2, eSection, { at: targetPosition } );

					const historyItem = HistoryHelper.getFirstItem().attributes;

					// Exist in history.
					HistoryHelper.inHistoryValidate( assert, historyItem, 'move', 'Column' );

					// Undo.
					HistoryHelper.undoValidate( assert, historyItem );

					const eColumnAfterUndo = eColumn2.lookup();

					assert.equal( eColumnAfterUndo.view._index, originalPosition,
						'Element has been returned to the original position' );

					// Redo.
					HistoryHelper.redoValidate( assert, historyItem );

					const eColumnAfterRedo = eColumn2.lookup();

					assert.equal( eColumnAfterRedo.view._index, targetPosition,
						'Element was re-added to correct position' );
				} );

				QUnit.test( 'Widget', ( assert ) => {
					const eSection = ElementsHelper.createSection(),
						eColumn1 = ElementsHelper.createColumn( eSection ),
						eColumn2 = ElementsHelper.createColumn( eSection ),
						eWidget = ElementsHelper.createButton( eColumn1 ),
						originalPosition = eWidget.view._index,
						targetPosition = 1;

					ElementsHelper.createButton( eColumn2 );
					ElementsHelper.createButton( eColumn2 );

					ElementsHelper.move( eWidget, eColumn2, { at: targetPosition } );

					const historyItem = HistoryHelper.getFirstItem().attributes;

					// Exist in history.
					HistoryHelper.inHistoryValidate( assert, historyItem, 'move', 'Button' );

					// Undo.
					HistoryHelper.undoValidate( assert, historyItem );

					const eWidgetAfterUndo = eWidget.lookup();

					assert.equal( eWidgetAfterUndo.view._index, originalPosition,
						'Element has been returned to the original position' );

					// Redo.
					HistoryHelper.redoValidate( assert, historyItem );

					const eWidgetAfterRedo = eWidget.lookup();

					assert.equal( eWidgetAfterRedo.view._index, targetPosition,
						'Element was re-added to correct position' );
				} );
			} );
		} );

		QUnit.module( 'Multiple Selection', () => {
			QUnit.test( 'Sections', ( assert ) => {
				// Create Section at 0.
				ElementsHelper.createSection();

				const section1ColumnsCount = 3,
					section2ColumnsCount = 4,
					eSection1 = ElementsHelper.createSection( section1ColumnsCount ),
					eSection2 = ElementsHelper.createSection( section2ColumnsCount );

				ElementsHelper.multiMove( [ eSection1, eSection2 ], elementor.getPreviewContainer(), { at: 0 } );

				// Validate first section have 3 columns.
				assert.equal( elementor.getPreviewContainer().model.attributes.elements.first().attributes.elements.length,
					section1ColumnsCount,
					`Section #1, '${ section1ColumnsCount }' columns were created.` );

				// Validate second section have 4 columns.
				assert.equal( elementor.getPreviewContainer().model.attributes.elements.at( 1 ).attributes.elements.length,
					section2ColumnsCount,
					`Section #2, '${ section2ColumnsCount }' columns were created.` );
			} );

			QUnit.test( 'Columns', ( assert ) => {
				const eSection1 = ElementsHelper.createSection(),
					eSection2 = ElementsHelper.createSection(),
					eColumn1 = ElementsHelper.createColumn( eSection1 ),
					eColumn2 = ElementsHelper.createColumn( eSection1 );

				ElementsHelper.multiMove( [ eColumn1, eColumn2 ], eSection2 );

				// Validate.
				assert.equal( eSection2.children.length, 3,
					'Columns were moved.' );
			} );

			QUnit.test( 'Widgets', ( assert ) => {
				const eSection = ElementsHelper.createSection(),
					eColumn1 = ElementsHelper.createColumn( eSection ),
					eColumn2 = ElementsHelper.createColumn( eSection ),
					eButton1 = ElementsHelper.createButton( eColumn1 ),
					eButton2 = ElementsHelper.createButton( eColumn1 );

				ElementsHelper.multiMove( [ eButton1, eButton2 ], eColumn2 );

				// Validate.
				assert.equal( eColumn1.children.length, 0,
					'Widgets were removed from the first column.' );
				assert.equal( eColumn2.children.length, 2,
					'Widgets were moved/create at the second column.' );
			} );
		} );

		QUnit.module( 'Misc', () => {
			QUnit.test( 'Swap column places -- Ensure valid containers', ( assert ) => {
				// Arrange.
				const eSection = ElementsHelper.createSectionStructure( 2 ),
					columnsIds = [],
					actualColumnsIds = [];

				eSection.children.forEach( ( eColumn ) => columnsIds.push( eColumn.id ) );

				// Act.
				ElementsHelper.move( eSection.children[ 0 ], eSection, { at: 1 } );

				eSection.children.forEach( ( eColumn ) => actualColumnsIds.push( eColumn.id ) );

				// Assert.
				assert.deepEqual( actualColumnsIds, columnsIds.reverse(),
					'The column ids actually reversed' );
			} );
		} );
	} );
};

export default Move;
