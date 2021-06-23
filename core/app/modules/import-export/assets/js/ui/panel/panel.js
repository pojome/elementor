import { arrayToClassName } from 'elementor-app/utils/utils.js';

import Card from 'elementor-app/ui/card/card';
import PanelHeader from './panel-header';
import PanelHeadline from './panel-headline';
import PanelBody from './panel-body';

import './panel.scss';

export default function Panel( props ) {
	return (
		<Card className={ arrayToClassName( [ 'e-app-import-export-panel', props.className ] ) }>
			{ props.children }
		</Card>
	);
}

Panel.propTypes = {
	className: PropTypes.string,
	children: PropTypes.any.isRequired,
};

Panel.defaultProps = {
	className: '',
};

Panel.Header = PanelHeader;
Panel.Headline = PanelHeadline;
Panel.Body = PanelBody;
