import ElementsHelper from '../helper';
import HistoryHelper from '../../history/helper';

export const Delete = () => {
	QUnit.module( 'Delete', () => {
		QUnit.module( 'Single Selection', () => {
			QUnit.test( 'Simple', ( assert ) => {
				const eColumn = ElementsHelper.createSection( 1, true ),
					eButton1 = ElementsHelper.createButton( eColumn ),
					eButton2 = ElementsHelper.createButton( eColumn );

				ElementsHelper.delete( eButton1 );

				// Validate.
				assert.equal( eColumn.children.length, 1, 'Button #1 were deleted.' );

				// Ensure editor saver.
				$e.internal( 'document/save/set-is-modified', { status: false } );

				ElementsHelper.delete( eButton2 );

				// Validate.
				assert.equal( eColumn.children.length, 0, 'Button #2 were deleted.' );

				assert.equal( elementor.saver.isEditorChanged(), true,
					'Command applied the saver editor is changed.' );
			} );

			QUnit.test( 'History', ( assert ) => {
				const eWidget = ElementsHelper.createAutoButton();

				ElementsHelper.delete( eWidget );

				const historyItem = HistoryHelper.getFirstItem().attributes;

				// Exist in history.
				HistoryHelper.inHistoryValidate( assert, historyItem, 'remove', 'Button' );

				// Undo.
				HistoryHelper.undoValidate( assert, historyItem );

				// Element exist again.
				HistoryHelper.recreatedValidate( assert, eWidget );

				// Redo.
				HistoryHelper.redoValidate( assert, historyItem );

				// Element Does not exist.
				HistoryHelper.destroyedValidate( assert, eWidget );
			} );
		} );

		QUnit.test( 'Multiple Selection', ( assert ) => {
			const eColumn = ElementsHelper.createSection( 1, true ),
				eButton1 = ElementsHelper.createButton( eColumn ),
				eButton2 = ElementsHelper.createButton( eColumn );

			ElementsHelper.multiDelete( [ eButton1, eButton2 ] );

			// Validate.
			assert.equal( eColumn.children.length, 0, 'Buttons were deleted.' );
		} );
	} );
};

export default Delete;
