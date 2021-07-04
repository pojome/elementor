export const Undock = () => {
	QUnit.module( 'Undock', () => {
		QUnit.test( 'Simple', ( assert ) => {
			if ( ! elementor.navigator.isDocked ) {
				$e.run( 'navigator/dock' );
			}

			$e.run( 'navigator/undock' );

			assert.equal( elementor.navigator.isDocked, false );
		} );
	} );
};

export default Undock;
