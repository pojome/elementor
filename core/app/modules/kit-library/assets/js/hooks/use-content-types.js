import ContentType from '../models/content-type';
import { useQuery } from 'react-query';

export const KEY = 'content-types';

/**
 * The data should come from the server, this is a temp solution that helps to demonstrate that data comes from the server
 * but for now this is a local data.
 *
 * @returns {UseQueryResult<Promise.constructor, unknown>}
 */
export default function useContentTypes() {
	return useQuery( [ KEY ], fetchContentTypes );
}

/**
 * @returns {Promise.constructor}
 */
function fetchContentTypes() {
	return Promise.resolve( [
		{
			id: 'page',
			label: __( 'Pages', 'elementor' ),
			doc_types: [ 'page' ],
			order: 0,
		},
		{
			id: 'site-parts',
			label: __( 'Site Parts', 'elementor' ),
			doc_types: [ 'header', 'footer', 'single', 'archive', '404' ],
			order: 1,
		},
		{
			id: 'popup',
			label: __( 'Popups', 'elementor' ),
			doc_types: [ 'popup' ],
			order: 2,
		},
	] ).then( ( data ) => {
		return data.map( ( contentType ) => ContentType.createFromResponse( contentType ) );
	} );
}
