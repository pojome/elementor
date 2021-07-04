export const Toggle = () => {
	QUnit.module( 'Toggle', () => {
		QUnit.test( 'Simple', ( assert ) => {
			const initialState = elementor.navigator.isOpen();

			$e.run( 'navigator/toggle' );

			assert.equal( elementor.navigator.isOpen(), ! initialState );

			$e.run( 'navigator/toggle' );

			assert.equal( elementor.navigator.isOpen(), initialState );
		} );
	} );
};

export default Toggle;
