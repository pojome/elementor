import { breakpoints } from './preview';
import { Button, Grid } from '@elementor/app-ui';

import './preview-responsive-controls.scss';

export default function PreviewResponsiveControls( props ) {
	return (
		<Grid container alignItems="center" justify="center" className="e-kit-library__preview-responsive-controls">
			{ breakpoints.map( ( { label, value } ) => {
				let className = 'e-kit-library__preview-responsive-controls-item';

				if ( props.active === value ) {
					className += ' e-kit-library__preview-responsive-controls-item--active';
				}

				return (
					<Button
						key={ value }
						text={ label }
						hideText={ true }
						className={ className }
						icon={ `eicon-device-${ value }` }
						onClick={ () => props.onChange( value ) }
					/>
				);
			} ) }
		</Grid>
	);
}

PreviewResponsiveControls.propTypes = {
	active: PropTypes.string,
	onChange: PropTypes.func.isRequired,
};

PreviewResponsiveControls.defaultProps = {
	active: 'desktop',
};
