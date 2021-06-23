import { useEffect, useRef } from 'react';
import './templates-features.scss';

export default function TemplatesFeatures( props ) {
	const featuresContainer = useRef( null ),
		isLockedFeatures = props.features.locked?.length,
		getLockedFeatures = () => {
			if ( ! isLockedFeatures ) {
				return;
			}

			return (
				<span ref={ featuresContainer } className={ props.isLocked ? 'e-app-export-templates-features__locked' : '' }>
					{ ', ' + props.features.locked.join( ', ' ) }
				</span>
			);
		},
		getOpenFeatures = () => props.features.open?.join( ', ' ),
		setTipsy = ( showTooltip ) => {
			const $featuresContainer = jQuery( featuresContainer.current ),
				displayMode = showTooltip ? 'show' : 'hide';

			if ( 'show' === displayMode ) {
				$featuresContainer.tipsy( {
					trigger: 'manual',
					gravity: 's',
					offset: 19,
					title() {
						return props.features.tooltip;
					},
				} );
			}

			$featuresContainer.tipsy( displayMode );
		};

	useEffect( () => {
		const isFeaturesWithTipsy = isLockedFeatures && props.isTipsyLibReady;

		if ( isFeaturesWithTipsy ) {
			setTipsy( props.showTooltip );
		}

		return () => {
			if ( isFeaturesWithTipsy ) {
				setTipsy( false );
			}
		};
	}, [ props.showTooltip, props.isTipsyLibReady ] );

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
	isTipsyLibReady: PropTypes.bool,
};

TemplatesFeatures.defaultProps = {
	showTooltip: false,
};
