import { arrayToClassName } from 'elementor-app/utils/utils.js';

import ModalProvider from 'elementor-app/ui/modal/modal';

export default function InfoModalTip( props ) {
	return <ModalProvider.Tip { ...props } className={ arrayToClassName( [ 'e-app-import-export-info-modal__tip', props.className ] ) } />;
}

InfoModalTip.propTypes = {
	className: PropTypes.string,
};

InfoModalTip.defaultProps = {
	className: '',
};
