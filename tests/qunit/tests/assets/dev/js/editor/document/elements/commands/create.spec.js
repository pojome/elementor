import ElementsHelper from '../helper';
import HistoryHelper from '../../history/helper';
import { DEFAULT_INNER_SECTION_COLUMNS } from 'elementor-elements/views/section';

export const Create = () => {
	QUnit.module( 'Create', () => {
		QUnit.module( 'Single Selection', () => {
			QUnit.test( 'Section', ( assert ) => {
				const eSection = ElementsHelper.createSection( 1 ),
					isSectionCreated = !! elementor.getPreviewContainer().children.find(
						( section ) => eSection.id === section.id
					);

				// Check.
				assert.equal( isSectionCreated, true, 'Section were created.' );
				assert.equal( elementor.saver.isEditorChanged(), true, 'Command applied the saver editor is changed.' );
			} );

			QUnit.test( 'Column', ( assert ) => {
				const eColumn = ElementsHelper.createSection( 1, true ),
					isColumnCreated = !! elementor.getPreviewContainer().children[ 0 ].children.find(
						( column ) => eColumn.id === column.id
					);

				// Check column exist.
				assert.equal( isColumnCreated, true, 'Column were created.' );
				assert.equal( elementor.saver.isEditorChanged(), true, 'Command applied the saver editor is changed.' );
			} );

			QUnit.test( 'Widget', ( assert ) => {
				const eColumn = ElementsHelper.createSection( 1, true ),
					eButton = ElementsHelper.createButton( eColumn ),
					isButtonCreated = !! elementor.getPreviewContainer().children[ 0 ].children[ 0 ].children.find(
						( widget ) => widget.id === eButton.id
					);

				// Check button exist.
				assert.equal( isButtonCreated, true, 'Button were created.' );
			} );

			QUnit.test( 'Widget: Inner Section', ( assert ) => {
				const eSection = ElementsHelper.createSection( 1 ),
					eColumn = eSection.children[ 0 ],
					eInnerSection = ElementsHelper.createInnerSection( eColumn ),
					isInnerSectionCreated = !! eColumn.children.find( ( widget ) => eInnerSection.id === widget.id );

				assert.equal( isInnerSectionCreated, true, 'inner section were created.' );
				assert.equal( eInnerSection.children.length, DEFAULT_INNER_SECTION_COLUMNS,
					`'${ DEFAULT_INNER_SECTION_COLUMNS }' columns were created in the inner section.` );
			} );

			QUnit.test( 'Widget: Custom Position', ( assert ) => {
				const eButton = ElementsHelper.createAutoButton();

				ElementsHelper.settings( eButton, {
					_position: 'absolute',
				} );

				const done = assert.async();

				setTimeout( () => {
					assert.equal( eButton.view.$el.hasClass( 'elementor-absolute' ), true, '',
						'Widget have "elementor-absolute" class.' );
					done();
				} );
			} );

			QUnit.module( 'History', () => {
				QUnit.test( 'Section', ( assert ) => {
					const eSection = ElementsHelper.createSection( 1 ),
						historyItem = HistoryHelper.getFirstItem().attributes;

					// Exist in history.
					HistoryHelper.inHistoryValidate( assert, historyItem, 'add', 'Section' );

					// Undo.
					HistoryHelper.undoValidate( assert, historyItem );

					// Element Does not exist.
					HistoryHelper.destroyedValidate( assert, eSection );

					// Redo.
					HistoryHelper.redoValidate( assert, historyItem );

					// Element exist again.
					HistoryHelper.recreatedValidate( assert, eSection );
				} );

				QUnit.test( 'Column', ( assert ) => {
					const eSection = ElementsHelper.createSection(),
						eColumn = ElementsHelper.createColumn( eSection ),
						historyItem = HistoryHelper.getFirstItem().attributes;

					// Exist in history.
					HistoryHelper.inHistoryValidate( assert, historyItem, 'add', 'Column' );

					// Undo.
					HistoryHelper.undoValidate( assert, historyItem );

					// Element Does not exist.
					HistoryHelper.destroyedValidate( assert, eColumn );

					// Redo.
					HistoryHelper.redoValidate( assert, historyItem );

					// Element exist again.
					HistoryHelper.recreatedValidate( assert, eColumn );
				} );

				QUnit.test( 'Widget', ( assert ) => {
					const eWidget = ElementsHelper.createAutoButton(),
						historyItem = HistoryHelper.getFirstItem().attributes;

					// Exist in history.
					HistoryHelper.inHistoryValidate( assert, historyItem, 'add', 'Button' );

					// Undo.
					HistoryHelper.undoValidate( assert, historyItem );

					// Element Does not exist.
					HistoryHelper.destroyedValidate( assert, eWidget );

					// Redo.
					HistoryHelper.redoValidate( assert, historyItem );

					// Element exist again.
					HistoryHelper.recreatedValidate( assert, eWidget );
				} );

				QUnit.test( 'Widget: Inner Section', ( assert ) => {
					const eColumn = ElementsHelper.createSection( 1, true ),
						eInnerSection = ElementsHelper.createInnerSection( eColumn ),
						historyItem = HistoryHelper.getFirstItem().attributes,
						innerSectionColumnsIds = [];

					eInnerSection.children.forEach( ( eInnerColumn ) => innerSectionColumnsIds.push( eInnerColumn.id ) );

					// Exist in history.
					HistoryHelper.inHistoryValidate( assert, historyItem, 'add', 'Inner Section' );

					// Inner section have x columns.
					assert.equal( eInnerSection.children.length, DEFAULT_INNER_SECTION_COLUMNS,
						`InnerSection have "${ DEFAULT_INNER_SECTION_COLUMNS }" columns` );

					// Undo.
					HistoryHelper.undoValidate( assert, historyItem );

					// Element Does not exist.
					HistoryHelper.destroyedValidate( assert, eInnerSection );

					// Redo.
					HistoryHelper.redoValidate( assert, historyItem );

					// Element exist again.
					HistoryHelper.recreatedValidate( assert, eInnerSection );

					const eInnerSectionAfterRedo = eInnerSection.lookup(),
						innerSectionAfterRedoColumnsIds = [];

					eInnerSectionAfterRedo.children.forEach( ( eInnerColumn ) => innerSectionAfterRedoColumnsIds.push( eInnerColumn.id ) );

					// Does two columns with the same ids as before.
					assert.equal( innerSectionAfterRedoColumnsIds.length, DEFAULT_INNER_SECTION_COLUMNS,
						`Inner Section have "${ DEFAULT_INNER_SECTION_COLUMNS } columns"` );
					assert.deepEqual( innerSectionAfterRedoColumnsIds, innerSectionColumnsIds,
						'Inner section columns have the same ids as before.' );
				} );
			} );
		} );

		QUnit.module( 'Multiple Selection', () => {
			QUnit.test( 'Columns', ( assert ) => {
				const eSection1 = ElementsHelper.createSection(),
					eSection2 = ElementsHelper.createSection(),
					eColumns = ElementsHelper.multiCreateColumn( [ eSection1, eSection2 ] );

				// Check columns exist.
				let count = 1;
				eColumns.forEach( ( eColumn ) => {
					const isColumnCreated = !! elementor.getPreviewContainer().children.find( ( section ) =>
						!! section.children.find( ( column ) => eColumn.id === column.id )
					);

					assert.equal( isColumnCreated, true, `Column #${ count } were created.` );
					++count;
				} );
			} );

			QUnit.test( 'Widgets', ( assert ) => {
				const eColumn1 = ElementsHelper.createSection( 1, true ),
					eColumn2 = ElementsHelper.createSection( 1, true ),
					eButtons = ElementsHelper.multiCreateButton( [ eColumn1, eColumn2 ] ),
					isButton1Created = !! eColumn1.children.find( ( widget ) => eButtons[ 0 ].id === widget.id ),
					isButton2Created = !! eColumn2.children.find( ( widget ) => eButtons[ 1 ].id === widget.id );

				// Check button exist.
				assert.equal( isButton1Created, true, 'Button #1 were created.' );
				assert.equal( isButton2Created, true, 'Button #2 were created.' );
			} );

			QUnit.module( 'History', () => {
				QUnit.test( 'Columns', ( assert ) => {
					const eSection1 = ElementsHelper.createSection(),
						eSection2 = ElementsHelper.createSection(),
						eColumns = ElementsHelper.multiCreateColumn( [ eSection1, eSection2 ] ),
						historyItem = HistoryHelper.getFirstItem().attributes;

					// Exist in history.
					HistoryHelper.inHistoryValidate( assert, historyItem, 'add', 'Column' );

					// Undo.
					HistoryHelper.undoValidate( assert, historyItem );

					// Elements Does not exist.
					eColumns.forEach( ( eColumn ) => HistoryHelper.destroyedValidate( assert, eColumn ) );

					// Redo.
					HistoryHelper.redoValidate( assert, historyItem );

					// Elements exist again.
					eColumns.forEach( ( eColumn ) => HistoryHelper.recreatedValidate( assert, eColumn ) );
				} );

				QUnit.test( 'Widgets', ( assert ) => {
					const eWidgets = ElementsHelper.multiCreateAutoButton(),
						historyItem = HistoryHelper.getFirstItem().attributes;

					// Exist in history.
					HistoryHelper.inHistoryValidate( assert, historyItem, 'add', 'Button' );

					// Undo.
					HistoryHelper.undoValidate( assert, historyItem );

					// Elements Does not exist.
					eWidgets.forEach( ( eWidget ) => HistoryHelper.destroyedValidate( assert, eWidget ) );

					// Redo.
					HistoryHelper.redoValidate( assert, historyItem );

					eWidgets.forEach( ( eWidget ) => HistoryHelper.recreatedValidate( assert, eWidget ) );
				} );

				QUnit.test( 'Widgets: Inner Section', ( assert ) => {
					const eColumn1 = ElementsHelper.createSection( 1, true ),
						eColumn2 = ElementsHelper.createSection( 1, true ),
						eInnerSections = ElementsHelper.multiCreateInnerSection( [ eColumn1, eColumn2 ] ),
						historyItem = HistoryHelper.getFirstItem().attributes,
						innerSectionColumnsIds = {};

					eInnerSections.forEach( ( eInnerSection ) => {
						if ( ! innerSectionColumnsIds[ eInnerSection.id ] ) {
							innerSectionColumnsIds[ eInnerSection.id ] = [];
						}

						eInnerSection.children.forEach( ( eInnerColumn ) => innerSectionColumnsIds[ eInnerSection.id ].push( eInnerColumn.id ) );
					} );

					// Exist in history.
					HistoryHelper.inHistoryValidate( assert, historyItem, 'add', 'Inner Section' );

					// Inner section have x columns.
					eInnerSections.forEach( ( eInnerSection ) => assert.equal( eInnerSection.children.length,
						DEFAULT_INNER_SECTION_COLUMNS, `InnerSection have "${ DEFAULT_INNER_SECTION_COLUMNS }" columns` ) );

					// Undo.
					HistoryHelper.undoValidate( assert, historyItem );

					// Elements Does not exist.
					eInnerSections.forEach( ( eInnerSection ) => HistoryHelper.destroyedValidate( assert, eInnerSection ) );

					// Redo.
					HistoryHelper.redoValidate( assert, historyItem );

					eInnerSections.forEach( ( eInnerSection ) => HistoryHelper.recreatedValidate( assert, eInnerSection ) );

					// Does two columns with the same ids as before.
					const innerSectionAfterRedoColumnsIds = {};

					eInnerSections.forEach( ( eInnerSection ) => {
						eInnerSection = eInnerSection.lookup();

						if ( ! innerSectionAfterRedoColumnsIds[ eInnerSection.id ] ) {
							innerSectionAfterRedoColumnsIds[ eInnerSection.id ] = [];
						}

						eInnerSection.children.forEach( ( eInnerColumn ) =>
							innerSectionAfterRedoColumnsIds[ eInnerSection.id ].push( eInnerColumn.id )
						);
					} );

					Object.values( innerSectionAfterRedoColumnsIds ).forEach( ( ids ) => {
						assert.equal( ids.length, DEFAULT_INNER_SECTION_COLUMNS,
							`Inner Section have "${ DEFAULT_INNER_SECTION_COLUMNS } columns"` );
					} );

					assert.deepEqual( innerSectionAfterRedoColumnsIds, innerSectionColumnsIds,
						'Inner section columns have the same ids as before.' );
				} );
			} );
		} );
	} );
};

export default Create;
