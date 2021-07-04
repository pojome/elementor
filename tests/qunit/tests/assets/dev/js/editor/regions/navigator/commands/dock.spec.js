export const Dock = () => {
	QUnit.module( 'Dock', () => {
		QUnit.test( 'Simple', ( assert ) => {
			if ( ! elementor.navigator.isDocked ) {
				$e.run( 'navigator/undock' );
			}

			$e.run( 'navigator/dock' );

			assert.equal( elementor.navigator.isDocked, true );
		} );
	} );
};

export default Dock;
