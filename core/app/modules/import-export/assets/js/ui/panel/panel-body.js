import { arrayToClassName } from 'elementor-app/utils/utils.js';

import Card from 'elementor-app/ui/card/card';

export default function PanelBody( props ) {
	return (
		<Card.Body padding={ props.padding } className={ arrayToClassName( [ 'e-app-import-export-panel__body', props.className ] ) }>
			{ props.children }
		</Card.Body>
	);
}

PanelBody.propTypes = {
	className: PropTypes.string,
	padding: PropTypes.string,
	children: PropTypes.any.isRequired,
};

PanelBody.defaultProps = {
	className: '',
	padding: '0',
};
