import { arrayToClassName } from 'elementor-app/utils/utils.js';

import ModalProvider from 'elementor-app/ui/modal/modal';

export default function InfoModalSection( props ) {
	return (
		<ModalProvider.Section className={ arrayToClassName( [ 'e-app-import-export-info-modal__section', props.className ] ) }>
			{ props.children }
		</ModalProvider.Section>
	);
}

InfoModalSection.propTypes = {
	className: PropTypes.string,
	children: PropTypes.any,
};

InfoModalSection.defaultProps = {
	className: '',
};
