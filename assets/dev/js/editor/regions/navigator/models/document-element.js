export default class DocumentElement extends Backbone.Model {
	getTitle() {
		return this.get( 'title' );
	}

	getIcon() {
		return this.get( 'icon' );
	}
}
