export const Open = () => {
	QUnit.module( 'Open', () => {
		QUnit.test( 'Simple', ( assert ) => {
			if ( elementor.navigator.isOpen() ) {
				$e.run( 'navigator/close' );
			}

			$e.run( 'navigator/open' );

			assert.equal( elementor.navigator.isOpen(), true );
		} );
	} );
};

export default Open;
