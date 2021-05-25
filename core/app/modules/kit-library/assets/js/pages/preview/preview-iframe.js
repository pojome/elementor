import { Grid } from '@elementor/app-ui';

import './preview-iframe.scss';

export function PreviewIframe( props ) {
	return (
		<Grid container justify="center" className="e-kit-library__preview-library-container">
			<iframe
				className="e-kit-library__preview-library"
				src={ props.previewUrl }
				style={ props.style }
			/>
		</Grid>
	);
}

PreviewIframe.propTypes = {
	previewUrl: PropTypes.string.isRequired,
	style: PropTypes.object,
};

PreviewIframe.defaultProps = {
	style: {
		width: '100%',
		height: '100%',
	},
};
