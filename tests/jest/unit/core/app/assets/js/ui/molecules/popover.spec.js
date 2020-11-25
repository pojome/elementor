import { render, fireEvent } from '@testing-library/react';
import Popover from 'elementor-app/ui/molecules/popover';

describe( '<Popover />', () => {
	it( 'should render the component', () => {
		// Arrange
		const text = 'Test children';

		// Act
		const component = render( <Popover> { text } </Popover> );

		// Assert
		expect( component.getByTestId( 'popover' ) ).toHaveTextContent( text );
	} );

	it( 'should call closeFunction prop when clicking on the popup', () => {
		// Arrange
		const mockFn = jest.fn();
		const component = render( <Popover closeFunction={mockFn} /> );

		// Act
		fireEvent.click( component.getByTestId( 'popover' ) );

		// Assert
		expect( mockFn ).toBeCalled();
	} );

	it( 'should call closeFunction prop when clicking on the background', () => {
		// Arrange
		const mockFn = jest.fn();
		const component = render( <Popover closeFunction={mockFn} /> );

		// Act
		fireEvent.click( component.getByTestId( 'background' ) );

		// Assert
		expect( mockFn ).toBeCalled();
	} );

	it( 'should match the snapshot', () => {
		// Arrange
		const { container } = render(
			<Popover className="test">
				<li> 1 </li>
				<li> 2 </li>
			</Popover>
		);

		// Act + Assert
		expect( container ).toMatchSnapshot();
	} );
} );
