import Taxonomy from '../models/taxonomy';
import { useQuery } from 'react-query';
import { useState, useCallback, useEffect } from 'react';

export const KEY = 'tags';

export default function useTaxonomies() {
	const [ force, setForce ] = useState( false );

	const forceRefetch = useCallback( () => setForce( true ), [ setForce ] );

	const query = useQuery( [ KEY ], () => fetchTaxonomies( force ) );

	useEffect( () => {
		if ( ! force ) {
			return;
		}

		query.refetch().then( () => setForce( false ) );
	}, [ force ] );

	return {
		...query,
		forceRefetch,
	};
}

function fetchTaxonomies( force ) {
	return $e.data.get( 'kit-taxonomies/index', {
		force: force ? 1 : undefined,
	}, { refresh: true } )
		.then( ( response ) => response.data )
		.then( ( { data } ) => data.map( ( taxonomy ) => Taxonomy.createFromResponse( taxonomy ) ) );
}
