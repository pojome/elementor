import BaseModel from './base-model';

export default class Document extends BaseModel {
	id = '';
	title = '';
	documentType = '';
	thumbnailUrl = '';
	previewUrl = '';

	/**
	 * Create a tag from server response
	 *
	 * @param document
	 */
	static createFromResponse( document ) {
		return new Document().init( {
			id: document.id,
			title: document.title,
			documentType: document.doc_type,
			thumbnailUrl: document.thumbnail_url,
			previewUrl: document.preview_url,
		} );
	}
}
