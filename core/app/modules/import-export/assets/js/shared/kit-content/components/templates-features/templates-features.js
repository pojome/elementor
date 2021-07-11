import Tooltip from 'elementor-app/molecules/tooltip';

import './templates-features.scss';

export default function TemplatesFeatures( props ) {
	const isLockedFeatures = props.features.locked?.length,
		getLockedFeatures = () => {
			if ( ! isLockedFeatures ) {
				return;
			}

			return (
				<Tooltip
					tag="span"
					offset={ 19 }
					show={ props.showTooltip }
					title={ props.features.tooltip }
					disabled={ ! props.isLocked }
					className={ props.isLocked ? 'e-app-export-templates-features__locked' : '' }
				>
					{ ', ' + props.features.locked.join( ', ' ) }
				</Tooltip>
			);
		},
		getOpenFeatures = () => props.features.open?.join( ', ' );

	return (
		<>
			{ getOpenFeatures() }
			{ getLockedFeatures() }
		</>
	);
}

TemplatesFeatures.propTypes = {
	features: PropTypes.object,
	isLocked: PropTypes.bool,
	showTooltip: PropTypes.bool,
};

TemplatesFeatures.defaultProps = {
	showTooltip: false,
};
