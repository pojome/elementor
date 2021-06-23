import './card.scss';

export default function CardImage( props ) {
	const image = <img src={ props.src } alt={ props.alt } className="eps-card__image" />;

	return (
		<figure className={ `eps-card__figure ${ props.className }` }>
			{ image }
			{ props.children }
		</figure>
	);
}

CardImage.propTypes = {
	className: PropTypes.string,
	src: PropTypes.string.isRequired,
	alt: PropTypes.string.isRequired,
	children: PropTypes.any,
};

CardImage.defaultProps = {
	className: '',
};
