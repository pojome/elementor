import './badge.scss';

export default function Badge( props ) {
	return (
		<span className={ `eps-badge eps-badge--${ props.variant } ${ props.className }` } style={ props.style }>
			{ props.children }
		</span>
	);
}

Badge.propTypes = {
	children: PropTypes.node,
	className: PropTypes.string,
	style: PropTypes.object,
	variant: PropTypes.oneOf( [ 'sm', 'md' ] ),
};

Badge.defaultProps = {
	className: '',
	style: {},
	variant: 'md',
};
