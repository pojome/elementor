import ElementsHelper from '../helper';
import HistoryHelper from '../../history/helper';

// TODO: Check code coverage and add required tests.
export const Paste = () => {
	QUnit.module( 'Paste', () => {
		QUnit.module( 'Single Selection', () => {
			QUnit.test( 'Simple', ( assert ) => {
				const eColumn = ElementsHelper.createSection( 1, true ),
					eButton = ElementsHelper.createButton( eColumn );

				ElementsHelper.copy( eButton );

				// Ensure editor saver.
				$e.internal( 'document/save/set-is-modified', { status: false } );

				ElementsHelper.paste( eColumn );

				// Check.
				assert.equal( elementor.elements.at( 0 ).get( 'elements' ).at( 0 ).get( 'elements' ).length, 2,
					'Pasted element were created.' );
				assert.equal( elementor.saver.isEditorChanged(), true,
					'Command applied the saver editor is changed.' );
			} );

			QUnit.test( 'History', ( assert ) => {
				const eColumn = ElementsHelper.createSection( 1, true ),
					eWidget = ElementsHelper.createButton( eColumn );

				ElementsHelper.copy( eWidget );

				const ePastedWidget = ElementsHelper.paste( eColumn ),
					historyItem = HistoryHelper.getFirstItem().attributes;

				// Exist in history.
				HistoryHelper.inHistoryValidate( assert, historyItem, 'paste', 'Elements' );

				// Undo.
				HistoryHelper.undoValidate( assert, historyItem );

				// Element Does not exist.
				HistoryHelper.destroyedValidate( assert, ePastedWidget );

				// Redo.
				HistoryHelper.redoValidate( assert, historyItem );

				// Element exist again.
				HistoryHelper.recreatedValidate( assert, ePastedWidget );
			} );
		} );

		QUnit.module( 'Multiple Selection', () => {
			QUnit.test( 'Simple', ( assert ) => {
				const eSection1 = ElementsHelper.createSection(),
					eSection2 = ElementsHelper.createSection(),
					eColumns = ElementsHelper.multiCreateColumn( [ eSection1, eSection2 ] ),
					eButtons = ElementsHelper.multiCreateButton( eColumns );

				ElementsHelper.copy( eButtons[ 0 ] );

				ElementsHelper.multiPaste( eColumns );

				// Check pasted button exist.
				let count = 1;
				eColumns.forEach( ( eColumn ) => {
					assert.equal( eColumn.children.length, 2,
						`Button #${ count } were pasted.` );
					++count;
				} );
			} );

			QUnit.test( 'History', ( assert ) => {
				const eColumn = ElementsHelper.createSection( 1, true ),
					eWidget = ElementsHelper.createButton( eColumn );

				ElementsHelper.copy( eWidget );

				const ePastedWidget = ElementsHelper.paste( eColumn ),
					historyItem = HistoryHelper.getFirstItem().attributes;

				// Exist in history.
				HistoryHelper.inHistoryValidate( assert, historyItem, 'paste', 'Elements' );

				// Undo.
				HistoryHelper.undoValidate( assert, historyItem );

				// Element Does not exist.
				HistoryHelper.destroyedValidate( assert, ePastedWidget );

				// Redo.
				HistoryHelper.redoValidate( assert, historyItem );

				// Element exist again.
				HistoryHelper.recreatedValidate( assert, ePastedWidget );
			} );
		} );
	} );
};

export default Paste;
