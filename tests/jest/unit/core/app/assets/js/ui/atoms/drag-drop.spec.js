import { render, fireEvent } from '@testing-library/react';
import DragDrop from 'elementor-app/ui/atoms/drag-drop';

describe( '<DragDrop />', () => {
	it( 'should have only base classes when there is no interaction with the drag area', () => {
		// Arrange + Act
		const { container } = render( <DragDrop /> );

		// Assert
		expect( container.firstChild ).toHaveClass( 'e-app-drag-drop' );
		expect( container.firstChild ).not.toHaveClass( 'e-app-drag-drop--drag-over' );
	} );

	it( 'should have drag over class and call drag over prop when dragging over', () => {
		// Arrange
		const mockDragOver = jest.fn();
		const { container } = render( <DragDrop onDragOver={mockDragOver} /> );

		// Act
		fireEvent.dragOver( container.firstChild );

		// Assert
		expect( container.firstChild ).toHaveClass( 'e-app-drag-drop' );
		expect( container.firstChild ).toHaveClass( 'e-app-drag-drop--drag-over' );
		expect( mockDragOver ).toHaveBeenCalled();
	} );

	it( 'should not add drag over class if the component in loading state', () => {
		// Arrange
		const { container } = render( <DragDrop isLoading={true} /> );

		// Act
		fireEvent.dragOver( container.firstChild );

		// Assert
		expect( container.firstChild ).toHaveClass( 'e-app-drag-drop' );
		expect( container.firstChild ).not.toHaveClass( 'e-app-drag-drop--drag-over' );
	} );

	it( 'should call onDrop prop when drop event fired', () => {
		// Arrange
		const mockDrop = jest.fn();
		const { container } = render( <DragDrop onDrop={mockDrop} /> );

		// Act
		fireEvent.drop( container.firstChild );

		// Assert
		expect( mockDrop ).toHaveBeenCalled();
	} );

	it( 'should call onDragLeave prop when dragLeave event fired', () => {
		// Arrange
		const mockDragLeave = jest.fn();
		const { container } = render( <DragDrop onDragLeave={mockDragLeave} /> );

		// Act
		fireEvent.dragLeave( container.firstChild );

		// Assert
		expect( mockDragLeave ).toHaveBeenCalled();
	} );

	it( 'should match the snapshot', () => {
		// Arrange
		const { container } = render(
			<DragDrop className="test" isLoading={false}>
				<div>
					This is a test
				</div>
			</DragDrop>
		);

		// Act + Assert
		expect( container ).toMatchSnapshot();
	} );
} );
