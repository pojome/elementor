import { Icon } from '@elementor/app-ui';

import './page-loader.scss';

export default function PageLoader( props ) {
	return (
		<div className={ `e-kit-library__page-loader ${ props.className }` }>
			<Icon className="eicon-loading eicon-animation-spin" />
		</div>
	);
}

PageLoader.propTypes = {
	className: PropTypes.string,
};

PageLoader.defaultProps = {
	className: '',
};
