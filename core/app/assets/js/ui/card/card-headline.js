import { arrayToClassName } from 'elementor-app/utils/utils.js';

import './card.scss';

export default function CardHeadline( props ) {
	const classNameBase = 'eps-card__headline',
		classes = [ classNameBase, props.className ];

	return (
		<h4 className={ arrayToClassName( classes ) }>
			{ props.children }
		</h4>
	);
}

CardHeadline.propTypes = {
	className: PropTypes.string,
	children: PropTypes.any.isRequired,
};

CardHeadline.defaultProps = {
	className: '',
};
