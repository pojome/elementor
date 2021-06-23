import { arrayToClassName } from 'elementor-app/utils/utils.js';

import './card.scss';

export default function CardDivider( props ) {
	const classNameBase = 'eps-card__divider',
		classes = [ classNameBase, props.className ];

	return (
		<hr className={ arrayToClassName( classes ) } />
	);
}

CardDivider.propTypes = {
	className: PropTypes.string,
};

CardDivider.defaultProps = {
	className: '',
};
