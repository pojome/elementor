import useContentTypes from './use-content-types';
import { useMemo } from 'react';

export default function useKitDocumentByType( kit ) {
	const contentTypesQuery = useContentTypes();

	const data = useMemo( () => {
		if ( ! kit || ! contentTypesQuery.data ) {
			return [];
		}

		return kit.getDocumentsByTypes( contentTypesQuery.data )
			.sort( ( a, b ) => a.order - b.order );
	}, [ kit, contentTypesQuery.data ] );

	return {
		...contentTypesQuery,
		data,
	};
}
