import ElementsHelper from 'elementor-tests-qunit/tests/assets/dev/js/editor/document/elements/helper';

export const ToggleFoldingAll = () => {
	QUnit.module( 'ToggleFoldingAll', () => {
		QUnit.test( 'Simple', ( assert ) => {
			ElementsHelper.empty();

			// Toggle possible states.
			const states = [ false, true ],
				sectionsCount = 2,
				eSections = [];

			for ( let i = 0; i < sectionsCount; i++ ) {
				eSections.push( ElementsHelper.createSection() );
			}

			// TODO:  To find the source of the issue with `timeout` see navigator->element->initialize method.
			const done = assert.async();

			setTimeout( () => {
				states.forEach( ( state ) => {
					assert.ok( true, `Testing with state = ${ state }` );

					// Toggle all.
					$e.run( 'navigator/elements/toggle-folding-all', { state } );

					eSections.forEach( ( eSection ) => {
						assert.equal( eSection.navigator.view.$el.children().hasClass( 'elementor-active' ), state,
							`section id: '${ eSection.id }' navigator element active state: '${ state }'` );
					} );
				} );

				done();
			} );
		} );
	} );
};

export default ToggleFoldingAll;
