import './popover.scss';

export default function Popover( props ) {
	return (
		<>
			<div className="eps-popover__background" onClick={ props.closeFunction } data-testid="background"/>
			<ul className={`eps-popover ${ props.className }`} onClick={ props.closeFunction } data-testid="popover">
				{ props.children }
			</ul>
		</>
	);
}

Popover.propTypes = {
	children: PropTypes.any.isRequired,
	className: PropTypes.string,
	closeFunction: PropTypes.func,
};

Popover.defaultProps = {
	className: '',
};
