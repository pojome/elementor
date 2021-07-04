import ElementsHelper from 'elementor-tests-qunit/tests/assets/dev/js/editor/document/elements/helper';

export const Hide = () => {
	QUnit.module( 'Hide', () => {
		QUnit.test( 'Simple', ( assert ) => {
			const eButton = ElementsHelper.createAutoButton(),
				done = assert.async();

			// TODO:  To find the source of the issue with `timeout` see navigator->element->initialize method.
			setTimeout( () => {
				$e.run( 'navigator/elements/hide', {
					container: eButton,
				} );

				assert.equal( eButton.model.get( 'hidden' ), true );

				done();
			} );
		} );
	} );
};

export default Hide;
