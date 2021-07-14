import { Heading, Text, Grid, Button } from '@elementor/app-ui';

import './error-screen.scss';

export default function ErrorScreen( props ) {
	return (
		<Grid container alignItems="center" justify="center" direction="column" className="e-kit-library__error-screen">
			<img src={ `${ elementorAppConfig.assets_url }images/no-search-results.svg` }/>
			<Heading
				tag="h3"
				variant="display-1"
				className="e-kit-library__error-screen-title"
			>
				{ props.title }
			</Heading>
			<Text variant="xl" className="e-kit-library__error-screen-description">
				{ props.description } { ' ' }
				<Button
					text={ props.button.text }
					color="link"
					onClick={ props.button.action }
					url={ props.button.url }
					target={ props.button.target }
				/>
			</Text>
		</Grid>
	);
}

ErrorScreen.propTypes = {
	title: PropTypes.string,
	description: PropTypes.string,
	button: PropTypes.shape( {
		text: PropTypes.string,
		action: PropTypes.func,
		url: PropTypes.string,
		target: PropTypes.string,
	} ),
};
