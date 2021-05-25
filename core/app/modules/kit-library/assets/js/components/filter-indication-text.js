import { sprintf } from '@wordpress/i18n';
import { Text, Button, Grid, Badge } from '@elementor/app-ui';
import { useMemo } from 'react';

import './filter-indication-text.scss';

export default function FilterIndicationText( props ) {
	const selectedTaxonomies = useMemo( () =>
			Object.values( props.filter.taxonomies ).reduce( ( current, groupedTaxonomies ) => [ ...current, ...groupedTaxonomies ] ),
		[ props.filter.taxonomies ]
	);

	const isFilterActive = useMemo(
		() =>
			props.filter.search ||
			selectedTaxonomies.length,
		[ props.filter ]
	);

	if ( ! isFilterActive ) {
		return '';
	}

	return (
		<Grid container className="e-kit-library__filter-indication">
			<Text className="e-kit-library__filter-indication-text">
				{ sprintf( __( 'Showing %s Results', 'elementor' ), props.resultCount ) }
				{ ' ' }
				{ props.filter.search && sprintf( __( 'For: "%s"', 'elementor' ), props.filter.search ) }
				{ ' ' }
				{ selectedTaxonomies.length > 0 && (
					<>
						{ __( 'In', 'elementor' ) }
						{ ' ' }
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
				text={ __( 'Clear All' ) }
				variant="underlined"
				onClick={ props.onClear }
			/>
		</Grid>
	);
}

FilterIndicationText.propTypes = {
	filter: PropTypes.shape( {
		search: PropTypes.string,
		taxonomies: PropTypes.objectOf( PropTypes.arrayOf( PropTypes.string ) ),
	} ),
	resultCount: PropTypes.number.isRequired,
	onClear: PropTypes.func.isRequired,
	onRemoveTag: PropTypes.func.isRequired,
};
