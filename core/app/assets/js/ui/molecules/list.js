import { pxToRem, arrayToClassName } from 'elementor-app/utils/utils.js';
import ListItem from './list-item';

import './list.scss';

export default function List( props ) {
	const baseClassName = 'eps-list',
		classes = [	baseClassName, props.className ];

	let style;

	if ( props.hasOwnProperty( 'padding' ) ) {
		style = {
			'--eps-list-padding': pxToRem( props.padding ),
		};

		classes.push( baseClassName + '--padding' );
	}

	if ( props.separated ) {
		classes.push( baseClassName + '--separated' );
	}

	return (
		<ul style={ style } className={ arrayToClassName( classes ) }>
			{ props.children }
		</ul>
	);
}

List.propTypes = {
	className: PropTypes.string,
	divided: PropTypes.any,
	separated: PropTypes.any,
	padding: PropTypes.string,
	children: PropTypes.oneOfType( [
		PropTypes.object,
		PropTypes.arrayOf( PropTypes.object ),
	] ).isRequired,
};

List.defaultProps = {
	className: '',
};

List.Item = ListItem;
