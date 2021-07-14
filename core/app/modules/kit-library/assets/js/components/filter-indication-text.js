import useSelectedTaxonomies from '../hooks/use-selected-taxonomies';
import Badge from './badge';
import { sprintf, _n } from '@wordpress/i18n';
import { Text, Button, Grid } from '@elementor/app-ui';

import './filter-indication-text.scss';

export default function FilterIndicationText( props ) {
	const selectedTaxonomies = useSelectedTaxonomies( props.queryParams.taxonomies );

	return (
		<Grid container className="e-kit-library__filter-indication">
			<Text className="e-kit-library__filter-indication-text">
				{ sprintf( _n( 'Showing %s result for', 'Showing %s results for', props.resultCount, 'elementor' ), ! props.resultCount ? __( 'no', 'elementor' ) : props.resultCount ) }
				{ ' ' }
				{ props.queryParams.search && `"${ props.queryParams.search }"` }
				{ ' ' }
				{ selectedTaxonomies.length > 0 && (
					<>
						{ selectedTaxonomies.map( ( taxonomy ) => (
							<Badge key={ taxonomy } className="e-kit-library__filter-indication-badge">
								{ taxonomy }
								<Button
									text={ __( 'Remove', 'elementor' ) }
									hideText={ true }
									icon="eicon-editor-close"
									className="e-kit-library__filter-indication-badge-remove"
									onClick={ () => props.onRemoveTag( taxonomy ) }
								/>
							</Badge>
						) ) }
					</>
				) }

			</Text>
			<Button
				className="e-kit-library__filter-indication-button"
				text={ __( 'Clear all', 'elementor' ) }
				variant="underlined"
				onClick={ props.onClear }
			/>
		</Grid>
	);
}

FilterIndicationText.propTypes = {
	queryParams: PropTypes.shape( {
		search: PropTypes.string,
		taxonomies: PropTypes.objectOf( PropTypes.arrayOf( PropTypes.string ) ),
		favorite: PropTypes.bool,
	} ),
	resultCount: PropTypes.number.isRequired,
	onClear: PropTypes.func.isRequired,
	onRemoveTag: PropTypes.func.isRequired,
};
