import { arrayToClassName, pxToRem } from 'elementor-app/utils/utils.js';

import './box.scss';

export default function Box( props ) {
	const baseClassName = 'eps-box',
		classes = [ baseClassName, props.className ],
		style = {};

	if ( props.hasOwnProperty( 'padding' ) ) {
		style[ '--eps-box-padding' ] = pxToRem( props.padding );

		classes.push( baseClassName + '--padding' );
	}

	return (
		<div style={ style } className={ arrayToClassName( classes ) }>
			{ props.children }
		</div>
	);
}

Box.propTypes = {
	className: PropTypes.string,
	padding: PropTypes.string,
	children: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.object,
		PropTypes.arrayOf( PropTypes.object ),
	] ).isRequired,
};

Box.defaultProps = {
	className: '',
};
