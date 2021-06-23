import BaseModel from './base-model';
import Document from './document';

export default class Kit extends BaseModel {
	id = '';
	title = '';
	description = '';
	isFavorite = false;
	thumbnailUrl = null;
	previewUrl = '';
	accessLevel = 0;
	trendIndex = null;
	popularityIndex = null;
	featuredIndex = null;
	createdAt = null;
	updatedAt = null;
	keywords = [];
	taxonomies = [];
	documents = [];

	/**
	 * Create a kit from server response
	 *
	 * @param kit
	 */
	static createFromResponse( kit ) {
		return new Kit().init( {
			id: kit.id,
			title: kit.title,
			description: kit.description,
			isFavorite: kit.is_favorite,
			thumbnailUrl: kit.thumbnail_url,
			previewUrl: kit.preview_url,
			accessLevel: kit.access_level,
			trendIndex: kit.trend_index,
			popularityIndex: kit.popularity_index,
			featuredIndex: kit.featured_index,
			// TODO: Remove when the API is stable (when date params always exists)
			createdAt: kit.created_at ? new Date( kit.created_at ) : null,
			updatedAt: kit.updated_at ? new Date( kit.updated_at ) : null,
			//
			keywords: kit.keywords,
			taxonomies: kit.taxonomies,
			documents: kit.documents ?
				kit.documents.map( ( document ) => Document.createFromResponse( document ) ) :
				[],
		} );
	}

	/**
	 * Get content types as param and group all the documents based on it.
	 *
	 * @param {ContentType[]} contentTypes
	 * @returns {ContentType[]}
	 */
	getDocumentsByTypes( contentTypes ) {
		return contentTypes.map( ( contentType ) => {
			contentType = contentType.clone();

			contentType.documents = this.documents
				.filter( ( document ) => contentType.documentTypes.includes( document.documentType ) );

			return contentType;
		} );
	}
}
