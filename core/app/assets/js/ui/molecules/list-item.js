import { pxToRem, arrayToClassName } from 'elementor-app/utils/utils.js';

export default function ListItem( props ) {
	const baseClassName = 'eps-list__item',
		classes = [ baseClassName, props.className ];

	let style;

	if ( props.hasOwnProperty( 'padding' ) ) {
		style = {
			'--eps-list-item-padding': pxToRem( props.padding ),
		};

		classes.push( baseClassName + '--padding' );
	}

	return (
		<li style={ style } className={ arrayToClassName( classes ) }>
			{ props.children }
		</li>
	);
}

ListItem.propTypes = {
	className: PropTypes.string,
	padding: PropTypes.string,
	children: PropTypes.any.isRequired,
};

ListItem.defaultProps = {
	className: '',
};
