import ModalProvider from 'elementor-app/ui/modal/modal';
import InfoModalSection from './info-modal-section';
import InfoModalHeading from './info-modal-heading';
import InfoModalText from './info-modal-text';
import InfoModalTip from './info-modal-tip';

import './info-modal.scss';

export const infoButtonProps = {
	id: 'info-modal',
	className: 'e-app-export-kit-information__info-icon',
	icon: 'eicon-info-circle',
	text: __( 'Kit Info', 'elementor' ),
	color: 'secondary',
	hideText: true,
};

export default function InfoModal( props ) {
	const attrs = {
			className: 'e-app-import-export-info-modal',
			setShow: props.setShow,
		};

	if ( props.hasOwnProperty( 'show' ) ) {
		attrs.show = props.show;
	} else {
		attrs.toggleButtonProps = infoButtonProps;
	}

	return (
		<ModalProvider { ...attrs } title={ props.title }>
			{ props.children }
		</ModalProvider>
	);
}

InfoModal.propTypes = {
	show: PropTypes.bool,
	setShow: PropTypes.func,
	title: PropTypes.string,
	children: PropTypes.any.isRequired,
};

InfoModal.Section = InfoModalSection;
InfoModal.Heading = InfoModalHeading;
InfoModal.Text = InfoModalText;
InfoModal.Tip = InfoModalTip;

