import { Heading, Text, Grid, Button } from '@elementor/app-ui';

import './index-no-results.scss';

export default function IndexNoResults( props ) {
	return (
		<Grid container alignItems="center" justify="center" direction="column" className="e-kit-library__not-results">
			<img src={ `${ elementorAppConfig.assets_url }images/no-search-results.svg` }/>
			<Heading
				tag="h3"
				variant="h1"
				className="e-kit-library__not-results-title"
			>
				{ props.title }
			</Heading>
			<Text variant="xl" className="e-kit-library__not-results-description">
				{ props.description }
				<br/>
				<Button text={ props.button.text } color="link" onClick={ props.button.action }/>
			</Text>
		</Grid>
	);
}

IndexNoResults.propTypes = {
	title: PropTypes.string,
	description: PropTypes.string,
	button: PropTypes.shape( {
		text: PropTypes.string,
		action: PropTypes.func,
	} ),
};
