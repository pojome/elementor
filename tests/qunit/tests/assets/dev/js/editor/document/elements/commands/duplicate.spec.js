import ElementsHelper from '../helper';
import HistoryHelper from '../../history/helper';

export const Duplicate = () => {
	QUnit.module( 'Duplicate', () => {
		QUnit.module( 'Single Selection', () => {
			QUnit.test( 'Simple', ( assert ) => {
				const eColumn = ElementsHelper.createSection( 1, true ),
					eButton = ElementsHelper.createButton( eColumn ),
					eButtonDuplicateCount = 2;

				for ( let i = 0; i < eButtonDuplicateCount; ++i ) {
					const eDuplicatedButton = ElementsHelper.duplicate( eButton );

					// Check if duplicated buttons have unique ids.
					assert.notEqual( eDuplicatedButton.id, eButton.id,
						`Duplicate button # ${ i + 1 } have unique id.` );
				}

				// Check duplicated button exist.
				assert.equal( eColumn.children.length, ( eButtonDuplicateCount + 1 ),
					`'${ eButtonDuplicateCount }' buttons were duplicated.` );
			} );

			QUnit.test( 'History', ( assert ) => {
				const eWidget = ElementsHelper.createAutoButton(),
					eWidgetDuped = ElementsHelper.duplicate( eWidget ),
					historyItem = HistoryHelper.getFirstItem().attributes;

				// Exist in history.
				HistoryHelper.inHistoryValidate( assert, historyItem, 'duplicate', 'Button' );

				// Undo.
				HistoryHelper.undoValidate( assert, historyItem );

				// Element Does not exist.
				HistoryHelper.destroyedValidate( assert, eWidgetDuped );

				// Redo.
				HistoryHelper.redoValidate( assert, historyItem );

				// Element exist again.
				HistoryHelper.recreatedValidate( assert, eWidgetDuped );
			} );
		} );

		QUnit.module( 'Multiple Selection', () => {
			QUnit.test( 'Simple', ( assert ) => {
				const eColumn1 = ElementsHelper.createSection( 1, true ),
					eColumn2 = ElementsHelper.createSection( 1, true ),
					eButtons = ElementsHelper.multiCreateButton( [ eColumn1, eColumn2 ] );

				ElementsHelper.multiDuplicate( eButtons );

				// Check duplicated button exist.
				assert.equal( eColumn1.children.length, 2, 'Two buttons were created.' );
				assert.equal( eColumn2.children.length, 2, 'Two buttons were duplicated.' );
			} );

			QUnit.test( 'History', ( assert ) => {
				const eWidgets = ElementsHelper.multiCreateAutoButton(),
					eWidgetsDuped = ElementsHelper.multiDuplicate( eWidgets ),
					historyItem = HistoryHelper.getFirstItem().attributes;

				// Exist in history.
				HistoryHelper.inHistoryValidate( assert, historyItem, 'duplicate', 'Elements' );

				// Undo.
				HistoryHelper.undoValidate( assert, historyItem );

				// Element Does not exist.
				eWidgetsDuped.forEach( ( eWidgetDuped ) =>
					HistoryHelper.destroyedValidate( assert, eWidgetDuped )
				);

				// Redo.
				HistoryHelper.redoValidate( assert, historyItem );

				// Element exist again.
				eWidgetsDuped.forEach( ( eWidgetDuped ) =>
					HistoryHelper.recreatedValidate( assert, eWidgetDuped )
				);
			} );
		} );
	} );
};

export default Duplicate;
