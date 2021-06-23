import { arrayToClassName } from 'elementor-app/utils/utils.js';

import Heading from 'elementor-app/ui/atoms/heading';

export default function InfoModalHeading( props ) {
	return (
		<Heading variant="h3" tag="h2" className={ arrayToClassName( [ 'e-app-import-export-info-modal__heading', props.className ] ) }>
			{ props.children }
		</Heading>
	);
}

InfoModalHeading.propTypes = {
	className: PropTypes.string,
	children: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.object,
		PropTypes.arrayOf( PropTypes.object ),
	] ).isRequired,
};

InfoModalHeading.defaultProps = {
	className: '',
};
