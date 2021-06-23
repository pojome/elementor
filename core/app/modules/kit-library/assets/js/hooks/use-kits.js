import Kit from '../models/kit';
import useSelectedTaxonomies from './use-selected-taxonomies';
import { taxonomyType } from '../models/taxonomy';
import { useQuery } from 'react-query';
import { useState, useMemo, useCallback, useEffect } from 'react';

export const KEY = 'kits';

/**
 * The default query params
 *
 * @type {object}
 */
export const defaultQueryParams = {
	favorite: false,
	search: '',
	taxonomies: taxonomyType.reduce( ( current, { key } ) => {
		return {
			...current,
			[ key ]: [],
		};
	}, {} ),
	order: {
		direction: 'desc',
		by: 'featuredIndex',
	},
};

const kitsPipeFunctions = {
	/**
	 * Filter by favorite
	 *
	 * @param data
	 * @param queryParams
	 * @returns {array}
	 */
	favoriteFilter: ( data, queryParams ) => {
		if ( ! queryParams.favorite ) {
			return data;
		}

		return data.filter( ( item ) => item.isFavorite );
	},

	/**
	 * filter by search term.
	 *
	 * @param data
	 * @param queryParams
	 * @returns {array}
	 */
	searchFilter: ( data, queryParams ) => {
		if ( ! queryParams.search ) {
			return data;
		}

		return data.filter( ( item ) => {
			const keywords = [ ...item.keywords, item.title ];
			const searchTerm = queryParams.search.toLowerCase();

			return keywords.some( ( keyword ) => keyword.toLowerCase().includes( searchTerm ) );
		} );
	},

	/**
	 * Filter by taxonomies.
	 * In each taxonomy type it use the OR operator and between types it uses the AND operator.
	 *
	 * @param data
	 * @param queryParams
	 * @returns {array}
	 */
	taxonomiesFilter: ( data, queryParams ) => {
		return Object.values( queryParams.taxonomies )
			.filter( ( taxonomies ) => taxonomies.length )
			.reduce( ( current, taxonomies ) => current.filter( ( item ) =>
				taxonomies.some( ( taxonomy ) =>
					item.taxonomies.some( ( itemTaxonomy ) => taxonomy === itemTaxonomy )
				) ),
				data
			);
	},

	/**
	 * Sort all the data by the "order" query param
	 *
	 * @param data
	 * @param queryParams
	 * @returns {array}
	 */
	sort: ( data, queryParams ) => {
		const order = queryParams.order;

		return data.sort( ( item1, item2 ) => {
			if ( 'asc' === order.direction ) {
				return item1[ order.by ] - item2[ order.by ];
			}

			return item2[ order.by ] - item1[ order.by ];
		} );
	},
};

/**
 * A util function to transform data throw transform functions
 *
 * @param functions
 * @returns {function(*=, ...[*]): *}
 */
function pipe( ...functions ) {
	return ( value, ...args ) =>
		functions.reduce(
			( currentValue, currentFunction ) => currentFunction( currentValue, ...args ),
			value
		);
}

/**
 * Fetch kits
 *
 * @param force
 * @returns {*}
 */
function fetchKits( force ) {
	return $e.data.get( 'kits/index', {
		force: force ? 1 : undefined,
	}, { refresh: true } )
		.then( ( response ) => response.data )
		.then( ( { data } ) => data.map( ( item ) => Kit.createFromResponse( item ) ) );
}

/**
 * Main function.
 *
 * @param initialQueryParams
 * @returns {object}
 */
export default function useKits( initialQueryParams = {} ) {
	const [ force, setForce ] = useState( false );
	const [ queryParams, setQueryParams ] = useState( () => ( {
		ready: false, // When the query param is ready to use (after parsing and assign the query param from the url)
		...defaultQueryParams,
		...initialQueryParams,
	} ) );

	const forceRefetch = useCallback( () => setForce( true ), [ setForce ] );

	const clearQueryParams = useCallback(
		() => setQueryParams( { ready: true, ...defaultQueryParams, ...initialQueryParams } ),
		[ setQueryParams ]
	);

	const query = useQuery( [ KEY ], () => fetchKits( force ) );

	const data = useMemo(
		() => ! query.data ?
			[] :
			pipe( ...Object.values( kitsPipeFunctions ) )( [ ...query.data ], queryParams ),
		[ query.data, queryParams ]
	);

	const selectedTaxonomies = useSelectedTaxonomies( queryParams.taxonomies );

	const isFilterActive = useMemo(
		() => !! queryParams.search || !! selectedTaxonomies.length,
		[ queryParams ]
	);

	useEffect( () => {
		if ( ! force ) {
			return;
		}

		query.refetch().then( () => setForce( false ) );
	}, [ force ] );

	return {
		...query,
		data,
		queryParams,
		setQueryParams,
		clearQueryParams,
		forceRefetch,
		isFilterActive,
	};
}
