import { useState, useRef, useEffect } from 'react';
import { arrayToClassName } from 'elementor-app/utils/utils.js';

import Button from 'elementor-app/ui/molecules/button';
import Grid from 'elementor-app/ui/grid/grid';
import Icon from 'elementor-app/ui/atoms/icon';
import Text from 'elementor-app/ui/atoms/text';

import ModalSection from './modal-section';
import ModalTip from './modal-tip';

import './modal.scss';

export default function ModalProvider( props ) {
	const [ show, setShow ] = useState( props.show ),
		hideModal = () => {
			setShow( false );

			// The purpose of the props.setShow is to sync an external state with the component inner state.
			if ( props.setShow ) {
				props.setShow( false );
			}
		},
		showModal = () => {
			setShow( true );

			// The purpose of the props.setShow is to sync an external state with the component inner state.
			if ( props.setShow ) {
				props.setShow( true );
			}
		},
		modalAttrs = {
			...props,
			show,
			hideModal,
			showModal,
		};

	useEffect( () => {
		// Sync with external state.
		setShow( props.show );
	}, [ props.show ] );

	return (
		<>
			{
				props.toggleButtonProps &&
				<Button { ...props.toggleButtonProps } onClick={ showModal } />
			}

			<Modal { ...modalAttrs }>
				{ props.children }
			</Modal>
		</>
	);
}

ModalProvider.propTypes = {
	children: PropTypes.node.isRequired,
	toggleButtonProps: PropTypes.object,
	title: PropTypes.string,
	icon: PropTypes.string,
	show: PropTypes.bool,
	setShow: PropTypes.func,
};

ModalProvider.defaultProps = {
	show: false,
};

ModalProvider.Section = ModalSection;
ModalProvider.Tip = ModalTip;

export const Modal = ( props ) => {
	const modalRef = useRef( null ),
		closeRef = useRef( null ),
		closeModal = ( e ) => {
			const node = modalRef.current,
				closeNode = closeRef.current,
				isInCloseNode = closeNode && closeNode.contains( e.target );

			// ignore if click is inside the modal
			if ( node && node.contains( e.target ) && ! isInCloseNode ) {
				return;
			}

			props.hideModal();
		};

	useEffect( () => {
		if ( props.show ) {
			document.addEventListener( 'mousedown', closeModal, false );
		}

		return () => document.removeEventListener( 'mousedown', closeModal, false );
	}, [ props.show ] );

	if ( ! props.show ) {
		return null;
	}

	return (
		<div className="eps-modal__overlay" onClick={ closeModal }>
			<div className={ arrayToClassName( [ 'eps-modal', props.className ] ) } ref={ modalRef } >
				<Grid container className="eps-modal__header" justify="space-between" alignItems="center">
					<Grid item>
						<Icon className={`eps-modal__icon ${ props.icon }`} />
						<Text className="title" tag="span">{ props.title }</Text>
					</Grid>
					<Grid item>
						<div className="eps-modal__close-wrapper" ref={ closeRef }>
							<Button text={ __( 'Close', 'elementor' ) } hideText icon="eicon-close" onClick={ props.closeModal } />
						</div>
					</Grid>
				</Grid>
				<div className="eps-modal__body">
					{ props.children }
				</div>
			</div>
		</div>
	);
};

Modal.propTypes = {
	className: PropTypes.string,
	children: PropTypes.any.isRequired,
	title: PropTypes.string.isRequired,
	icon: PropTypes.string,
	show: PropTypes.bool,
	setShow: PropTypes.func,
	hideModal: PropTypes.func,
	showModal: PropTypes.func,
	closeModal: PropTypes.func,
};

Modal.defaultProps = {
	className: '',
};
