import ElementsHelper from '../../../../../elements/helper';
import HistoryHelper from '../../../../../history/helper';

export const SectionColumnsResetLayout = () => {
	QUnit.module( 'SectionColumnsResetLayout', () => {
		QUnit.test( 'Create', ( assert ) => {
			const eSection = ElementsHelper.createSection( 1 ),
				eColumn = ElementsHelper.createColumn( eSection );

			assert.equal( eColumn.settings.attributes._column_size, 50 );
		} );

		QUnit.test( 'After undo', ( assert ) => {
			const eSection = ElementsHelper.createSection( 2 ),
				firstColumn = eSection.children[ 0 ];

			// Validate the first column `_column_size`, is 50.
			assert.equal( firstColumn.settings.attributes._column_size, 50,
				'firstColumn "_column_size" is "50"' );

			// 3rd column.
			ElementsHelper.createColumn( eSection );

			// Will remove 3rd column.
			HistoryHelper.undoValidate( assert, HistoryHelper.getFirstItem().attributes );

			assert.equal( firstColumn.settings.attributes._column_size, 50,
				'firstColumn "_column_size" is "50" after undo' );
		} );

		QUnit.test( 'Deep', ( assert ) => {
			// Covers issue when creating a section then add column, then undo column, undo section.
			// redo section redo column, undo column, wrong columns size.

			const eSection = ElementsHelper.createSection( 2 );

			// 3rd column.
			ElementsHelper.createColumn( eSection );

			// Undo column.
			$e.run( 'document/history/undo' );

			// Undo section.
			$e.run( 'document/history/undo' );

			// Redo section.
			$e.run( 'document/history/redo' );

			// Redo column.
			$e.run( 'document/history/redo' );

			// Undo column.
			$e.run( 'document/history/undo' );

			let count = 0;
			eSection.lookup().children.forEach( ( eColumn ) => {
				// Validate all child(s) have '_column_size = 50'.
				assert.equal( eColumn.settings.attributes._column_size, 50,
					`Column #${ count }, _column_size is "50"` );

				count++;
			} );
		} );
	} );
};

export default SectionColumnsResetLayout;
