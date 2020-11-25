import { useState } from 'react';

import Utils from '../../utils/utils';

import './drag-drop.scss';

export default function DragDrop( props ) {
	const [ isDragOver, setIsDragOver ] = useState( false ),
		getClassName = () => {
			const baseClassName = 'e-app-drag-drop',
				classes = [ baseClassName, props.className ];

			if ( isDragOver && ! props.isLoading ) {
				classes.push( baseClassName + '--drag-over' );
			}

			return Utils.arrayToClassName( classes );
		},
		onDragDropActions = ( event ) => {
			event.preventDefault();
			event.stopPropagation();
		},
		dragDropEvents = {
			onDrop: ( event ) => {
				onDragDropActions( event );

				setIsDragOver( false );

				if ( props.onDrop ) {
					// TODO: if isLoading=true do we still want to call the method?
					props.onDrop( event );
				}
			},
			onDragOver: ( event ) => {
				onDragDropActions( event );

				setIsDragOver( true );

				if ( props.onDragOver ) {
					props.onDragOver( event );
				}
			},
			onDragLeave: ( event ) => {
				onDragDropActions( event );

				setIsDragOver( false );

				if ( props.onDragLeave ) {
					props.onDragLeave( event );
				}
			},
		};

	return (
		<div { ...dragDropEvents } className={ getClassName() }>
			{ props.children }
		</div>
	);
}

DragDrop.propTypes = {
	className: PropTypes.string,
	children: PropTypes.any,
	onDrop: PropTypes.func,
	onDragLeave: PropTypes.func,
	onDragOver: PropTypes.func,
	isLoading: PropTypes.bool,
};

DragDrop.defaultProps = {
	className: '',
};
