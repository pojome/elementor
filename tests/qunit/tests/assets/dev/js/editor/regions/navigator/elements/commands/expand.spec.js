import ElementsHelper from 'elementor-tests-qunit/tests/assets/dev/js/editor/document/elements/helper';

export const Expand = () => {
	QUnit.module( 'Expand', () => {
		QUnit.test( 'Simple', async ( assert ) => {
			const eWidget = ElementsHelper.createAutoButton(),
				eColumn = eWidget.parent,
				eSection = eColumn.parent,
				all = [ eSection, eColumn, eWidget ];

			assert.expect( all.length );

			// TODO:  To find the source of the issue with `timeout` see navigator->element->initialize method.
			setTimeout( () => {
				// Collapse all
				$e.run( 'navigator/elements/toggle-folding-all', { state: false } );
			} );

			const promises = all.map( ( container ) => new Promise( ( resolve ) => {
				setTimeout( () => {
					$e.run( 'navigator/elements/expand', { container } );

					assert.equal( container.navigator.view.$el.children().hasClass( 'elementor-active' ), true );

					resolve();
				} );
			} ) );

			await Promise.all( promises );
		} );
	} );
};

export default Expand;
