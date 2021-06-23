import { arrayToClassName } from 'elementor-app/utils/utils.js';

import Card from 'elementor-app/ui/card/card';

export default function PanelHeadline( props ) {
	return (
		<Card.Headline className={ arrayToClassName( [ 'e-app-import-export-panel__headline', props.className ] ) }>
			{ props.children }
		</Card.Headline>
	);
}

PanelHeadline.propTypes = {
	className: PropTypes.string,
	children: PropTypes.any.isRequired,
};

PanelHeadline.defaultProps = {
	className: '',
};
