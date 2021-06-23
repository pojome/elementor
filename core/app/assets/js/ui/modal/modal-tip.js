import { arrayToClassName } from '../../utils/utils';

import Heading from 'elementor-app/ui/atoms/heading';
import Text from 'elementor-app/ui/atoms/text';

export default function ModalTip( props ) {
	return (
		<div className={ arrayToClassName( [ 'eps-modal__tip', props.className ] ) }>
			<Heading variant="h3" tag="h3">{ props.title }</Heading>
			{ props.description && <Text variant="xs">{ props.description }</Text> }
		</div>
	);
}

ModalTip.propTypes = {
	className: PropTypes.string,
	title: PropTypes.string,
	description: PropTypes.string,
};

ModalTip.defaultProps = {
	className: '',
	title: __( 'Tip', 'elementor' ),
};
