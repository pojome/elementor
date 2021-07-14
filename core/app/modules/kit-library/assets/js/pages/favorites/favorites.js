import Index from '../index/index';
import ErrorScreen from '../../components/error-screen';
import { useNavigate } from '@reach/router';

export default function Favorites( props ) {
	const navigate = useNavigate();

	const indexNotResultsFavorites = <ErrorScreen
		title={ __( 'No favorites here yet...', 'elementor' ) }
		description={ __( 'Use the heart icon to save kits that inspire you. You\'ll be able to find them here.', 'elementor' ) }
		button={ {
			text: __( 'Continue browsing.', 'elementor' ),
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
