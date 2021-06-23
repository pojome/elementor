import { useKitFavoritesMutations } from '../hooks/use-kit-favorites-mutations';
import { Button } from '@elementor/app-ui';

import './favorites-actions.scss';

export default function FavoritesActions( props ) {
	const { addToFavorites, removeFromFavorites, isLoading } = useKitFavoritesMutations();

	const loadingClasses = isLoading ? 'e-kit-library__kit-favorite-actions--loading' : '';

	return (
		props.isFavorite ?
			<Button
				text={ __( 'Remove From Favorites', 'elementor' ) }
				hideText={ true }
				icon="eicon-heart"
				className={ `e-kit-library__kit-favorite-actions e-kit-library__kit-favorite-actions--active ${ loadingClasses }` }
				onClick={ () => ! isLoading && removeFromFavorites.mutate( props.id ) }
			/> :
			<Button
				text={ __( 'Add To Favorites', 'elementor' ) }
				hideText={ true }
				icon="eicon-heart-o"
				className={ `e-kit-library__kit-favorite-actions ${ loadingClasses }` }
				onClick={ () => ! isLoading && addToFavorites.mutate( props.id ) }
			/>
	);
}

FavoritesActions.propTypes = {
	isFavorite: PropTypes.bool,
	id: PropTypes.string,
};
