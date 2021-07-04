export const Close = () => {
	QUnit.module( 'Close', () => {
		QUnit.test( 'Simple', ( assert ) => {
			if ( ! elementor.navigator.isOpen() ) {
				$e.run( 'navigator/open' );
			}

			$e.run( 'navigator/close' );

			assert.equal( elementor.navigator.isOpen(), false );
		} );
	} );
};

export default Close;
