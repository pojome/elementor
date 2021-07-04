import ElementsHelper from 'elementor-tests-qunit/tests/assets/dev/js/editor/document/elements/helper';

export const Show = () => {
	QUnit.module( 'Show', () => {
		QUnit.test( 'Simple', ( assert ) => {
			const eButton = ElementsHelper.createAutoButton(),
				done = assert.async();

			// TODO:  To find the source of the issue with `timeout` see navigator->element->initialize method.
			setTimeout( () => {
				$e.run( 'navigator/elements/show', {
					container: eButton,
				} );

				assert.equal( eButton.model.get( 'hidden' ), false );

				done();
			} );
		} );
	} );
};

export default Show;
