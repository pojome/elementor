import BaseModel from './base-model';

export default class ContentType extends BaseModel {
	id = '';
	label = '';
	documentTypes = [];
	documents = [];
	order = 0;

	static createFromResponse( documentType ) {
		return new ContentType().init( {
			id: documentType.id,
			label: documentType.label,
			documentTypes: documentType.doc_types,
			order: documentType.order,
			documents: [],
		} );
	}
}
