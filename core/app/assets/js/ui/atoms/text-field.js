import { arrayToClassName } from '../../utils/utils';

import './text-field.scss';

export default function TextField( props ) {
	const classNameBase = 'eps-text-field',
		classes = [ classNameBase, props.className, { [ classNameBase + '--outlined' ]: 'outlined' === props.variant } ],
		validProps = { ...props, className: arrayToClassName( classes ) };

	if ( validProps.multiline ) {
		delete validProps.multiline;

		return (
			<textarea { ...validProps } />
		);
	}

	return (
		<input { ...validProps } type="text" />
	);
}

TextField.propTypes = {
	className: PropTypes.string,
	multiline: PropTypes.bool,
	variant: PropTypes.oneOf( [ 'standard', 'outlined' ] ),
	children: PropTypes.string,
};

TextField.defaultProps = {
	className: '',
	variant: 'standard',
};
