import CardHeader from './card-header';
import CardBody from './card-body';
import CardImage from './card-image';
import CardOverlay from './card-overlay';
import CardFooter from './card-footer';
import CardHeadline from './card-headline';
import CardDivider from './card-divider';

import './card.scss';

const Card = React.forwardRef( ( props, ref ) => {
	return (
		<article className={ `eps-card ${ props.className }` } ref={ ref }>
			{ props.children }
		</article>
	);
} );

Card.propTypes = {
	type: PropTypes.string,
	className: PropTypes.string,
	children: PropTypes.any,
};

Card.defaultProps = {
	className: '',
};

Card.displayName = 'Card';

Card.Header = CardHeader;
Card.Body = CardBody;
Card.Image = CardImage;
Card.Overlay = CardOverlay;
Card.Footer = CardFooter;
Card.Headline = CardHeadline;
Card.Divider = CardDivider;

export default Card;
