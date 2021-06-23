import { arrayToClassName } from '../../utils/utils';

export default function ModalSection( props ) {
	return (
		<section className={ arrayToClassName( [ 'eps-modal__section', props.className ] ) }>
			{ props.children }
		</section>
	);
}

ModalSection.propTypes = {
	className: PropTypes.string,
	children: PropTypes.any,
};

ModalSection.defaultProps = {
	className: '',
};
