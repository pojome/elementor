import { arrayToClassName, pxToRem } from 'elementor-app/utils/utils.js';

import './card.scss';

export default function CardHeader( props ) {
	const classNameBase = 'eps-card__header',
		classes = [ classNameBase, props.className ],
		style = {};

	if ( props.hasOwnProperty( 'padding' ) ) {
		style[ '--eps-card-header-padding' ] = pxToRem( props.padding );

		classes.push( classNameBase + '--padding' );
	}

	return (
		<header className={ arrayToClassName( classes ) } style={ style }>
			{ props.children }
		</header>
	);
}

CardHeader.propTypes = {
	className: PropTypes.string,
	padding: PropTypes.string,
	passive: PropTypes.bool,
	active: PropTypes.bool,
	children: PropTypes.any.isRequired,
};

CardHeader.defaultProps = {
	className: '',
};
