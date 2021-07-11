import React, { useState, useEffect, useRef } from 'react';
import { arrayToClassName } from '../utils/utils';

export default function Tooltip( props ) {
	const baseClassName = 'e-app-tooltip',
		classes = [ baseClassName, props.className ],
		childRef = useRef( null ),
		isAborted = useRef( false ),
		isManualControl = props.hasOwnProperty( 'show' ),
		[ isLibraryLoaded, setIsLibraryLoaded ] = useState( false ),
		[ showTooltip, setShowTooltip ] = useState( false ),
		directionsMap = {
			top: 's',
			right: 'w',
			down: 'n',
			left: 'e',
		},
		tipsyConfig = {
			trigger: isManualControl ? 'manual' : 'hover',
			gravity: directionsMap[ props.direction ],
			offset: props.offset,
			title() {
				return props.title;
			},
		},
		setTipsy = () => {
			const $tooltipContainer = jQuery( childRef.current );

			$tooltipContainer.tipsy( tipsyConfig );

			if ( isManualControl ) {
				const displayMode = showTooltip ? 'show' : 'hide';

				$tooltipContainer.tipsy( displayMode );
			}
		};

	useEffect( () => {
		// In case that the component is disabled the tipsy library will not be loaded by default.
		if ( ! props.disabled ) {
			isAborted.current = false;

			import(
				/* webpackIgnore: true */
				`${ elementorCommon.config.urls.assets }lib/tipsy/tipsy.min.js?ver=1.0.0`
			).then( () => {
				if ( ! isAborted.current ) {
					if ( isLibraryLoaded ) {
						setTipsy();
					} else {
						setIsLibraryLoaded( true );
					}
				}
			} );
		}

		return () => {
			if ( ! props.disabled ) {
				// Aborting the current dynamic-import state update in case of re-render.
				isAborted.current = true;

				// Cleanup of existing tipsy element in case of re-render.
				jQuery( '.tipsy:last' ).remove();
			}
		};
	}, [ props.disabled ] );

	useEffect( () => {
		if ( isLibraryLoaded ) {
			setTipsy();
		}
	}, [ isLibraryLoaded, showTooltip ] );

	useEffect( () => {
		// The "show" state should not be changed while the component is disabled.
		if ( ! props.disabled && props.show !== showTooltip ) {
			setShowTooltip( props.show );
		}
	}, [ props.show ] );

	return (
		<props.tag className={ arrayToClassName( classes ) } ref={ childRef }>
			{ props.children }
		</props.tag>
	);
}

Tooltip.propTypes = {
	className: PropTypes.string,
	offset: PropTypes.number,
	show: PropTypes.bool,
	direction: PropTypes.oneOf( [ 'top', 'right', 'left', 'down' ] ),
	tag: PropTypes.string.isRequired,
	title: PropTypes.string.isRequired,
	disabled: PropTypes.bool,
	children: PropTypes.any,
};

Tooltip.defaultProps = {
	className: '',
	offset: 10,
	direction: 'top',
	disabled: false,
};
