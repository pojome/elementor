import { arrayToClassName } from 'elementor-app/utils/utils.js';

import Card from 'elementor-app/ui/card/card';

export default function PanelHeader( props ) {
	return (
		<Card.Header padding="20" className={ arrayToClassName( [ 'e-app-import-export-panel__header', props.className ] ) }>
			{ props.children }
		</Card.Header>
	);
}

PanelHeader.propTypes = {
	className: PropTypes.string,
	padding: PropTypes.string,
	children: PropTypes.any.isRequired,
};

PanelHeader.defaultProps = {
	className: '',
	padding: '20',
};
