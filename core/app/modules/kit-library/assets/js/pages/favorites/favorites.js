import Index from '../index/index';
import IndexNoResults from '../index/index-no-results';
import { useNavigate } from '@reach/router';

export default function Favorites( props ) {
	const navigate = useNavigate();

	const indexNotResultsFavorites = <IndexNoResults
		title={ __( 'No favorites here yet...', 'elementor' ) }
		description={ __( 'Use the heart icon to save kits that inspire you. You\'ll be able to find them here', 'elementor' ) }
		button={ {
			text: __( 'Continue Browsing', 'elementor' ),
			action: () => navigate( '/kit-library' ),
		} }
	/>;

	return <Index
		path={ props.path }
		initialQueryParams={ { favorite: true } }
		renderNoResultsComponent={ ( { defaultComponent, isFilterActive } ) => {
			if ( ! isFilterActive ) {
				return indexNotResultsFavorites;
			}

			return defaultComponent;
		} }
	/>;
}

Favorites.propTypes = {
	path: PropTypes.string,
};
