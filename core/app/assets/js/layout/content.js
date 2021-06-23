export default function Content( props ) {
	return (
		<main className={ `eps-app__content ${ props.className }` }>
			{ props.children }
		</main>
	);
}

Content.propTypes = {
	children: PropTypes.any,
	className: PropTypes.string,
};

Content.defaultProps = {
	className: '',
};
