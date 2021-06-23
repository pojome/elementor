import ElementsHelper from '../../../../../elements/helper';

export const CreateColumnSectionEmpty = () => {
	QUnit.module( 'CreateColumnSectionEmpty', () => {
		QUnit.test( 'Move last standing column to another section - ensure no empty section', ( assert ) => {
			// Arrange.
			const eSectionA = ElementsHelper.createSection(),
				eSectionB = ElementsHelper.createSection(),
				eColumnOfSectionA = eSectionA.children[ 0 ];

			// Act.
			ElementsHelper.move( eColumnOfSectionA, eSectionB );

			// Assert.
			assert.equal( eSectionA.children.length, 1,
				'New column were created' );
		} );
	} );
};

export default CreateColumnSectionEmpty;
