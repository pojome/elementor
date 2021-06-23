import { useRef, useEffect } from 'react';
import { Grid } from '@elementor/app-ui';

export function PreviewIframe( props ) {
	const ref = useRef();

	useEffect( () => {
		if ( ! ref.current ) {
			return;
		}

		const listener = () => props.onLoaded();

		ref.current.addEventListener( 'load', listener );

		return () => ref.current && ref.current.removeEventListener( 'load', listener );
	}, [ ref.current, props.previewUrl ] );

	return (
		<Grid container justify="center" className="e-kit-library__preview-iframe-container">
			<iframe
				className="e-kit-library__preview-iframe"
				src={ props.previewUrl }
				style={ props.style }
				ref={ ref }
			/>
		</Grid>
	);
}

PreviewIframe.propTypes = {
	previewUrl: PropTypes.string.isRequired,
	style: PropTypes.object,
	onLoaded: PropTypes.func,
};

PreviewIframe.defaultProps = {
	style: {
		width: '100%',
		height: '100%',
	},
	onLoaded: () => {},
};
