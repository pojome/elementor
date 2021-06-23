import Taxonomy from '../models/taxonomy';
import Collapse from './collapse';
import SearchInput from './search-input';
import { Checkbox, Text } from '@elementor/app-ui';
import { sprintf } from '@wordpress/i18n';
import { useState, useMemo } from 'react';

const MIN_TAGS_LENGTH_FOR_SEARCH_INPUT = 15;

export default function TaxonomiesFilterList( props ) {
	const [ isOpen, setIsOpen ] = useState( props.taxonomiesByType.isOpenByDefault );
	const [ search, setSearch ] = useState( '' );

	const taxonomies = useMemo( () => {
		if ( ! search ) {
			return props.taxonomiesByType.data;
		}

		const lowerCaseSearch = search.toLowerCase();

		return props.taxonomiesByType.data.filter(
			( tag ) => tag.text.toLowerCase().includes( lowerCaseSearch )
		);
	}, [ props.taxonomiesByType.data, search ] );

	return (
		<Collapse
			className="e-kit-library__tags-filter-list"
			title={ props.taxonomiesByType.label }
			isOpen={ isOpen }
			onChange={ setIsOpen }
		>
			{
				props.taxonomiesByType.data.length >= MIN_TAGS_LENGTH_FOR_SEARCH_INPUT &&
					<SearchInput
						size="sm"
						className="e-kit-library__tags-filter-list-search"
						placeholder={ sprintf( __( 'Search %s...', 'elementor' ), props.taxonomiesByType.label ) }
						value={ search }
						onChange={ setSearch }
					/>
			}
			<div className="e-kit-library__tags-filter-list-container">
				{ 0 === taxonomies.length && <Text>{ __( 'No Results Found', 'elementor' ) }</Text> }
				{
					taxonomies.map( ( taxonomy ) => (
						<label key={ taxonomy.text } className="e-kit-library__tags-filter-list-item">
							<Checkbox
								checked={ props.selected[ taxonomy.type ]?.includes( taxonomy.text ) || false }
								onChange={ ( e ) => {
									const checked = e.target.checked;

									props.onSelect( taxonomy.type, ( prev ) => {
										return checked ?
											[ ...prev, taxonomy.text ] :
											prev.filter( ( tagId ) => tagId !== taxonomy.text );
									} );
								} }/>
							{ taxonomy.text }
						</label>
					) )
				}
			</div>
		</Collapse>
	);
}

TaxonomiesFilterList.propTypes = {
	taxonomiesByType: PropTypes.shape( {
		key: PropTypes.string,
		label: PropTypes.string,
		data: PropTypes.arrayOf( PropTypes.instanceOf( Taxonomy ) ),
		isOpenByDefault: PropTypes.bool,
	} ),
	selected: PropTypes.objectOf( PropTypes.arrayOf( PropTypes.string ) ),
	onSelect: PropTypes.func,
};
