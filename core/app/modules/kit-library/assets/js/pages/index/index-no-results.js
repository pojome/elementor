import { Heading, Text, Grid } from '@elementor/app-ui';

import './index-no-results.scss';

export default function IndexNoResults() {
	return (
		<Grid container alignItems="center" justify="center" direction="column" className="e-kit-library__not-results">
			<img src={ `${ elementorAppConfig.assets_url }images/no-search-results.svg` }/>
			<Heading
				tag="h3"
				variant="h1"
				className="e-kit-library__not-results-title"
			>
				{ __( 'No Results Found', 'elementor' ) }
			</Heading>
			<Text variant="xl" className="e-kit-library__not-results-description">
				{ __( 'Try retyping another keyword or explore the most popular kits bellow.', 'elementor' ) }
			</Text>
		</Grid>
	);
}
