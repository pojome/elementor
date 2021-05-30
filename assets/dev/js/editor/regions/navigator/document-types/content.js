import Base from './base';

export class Content extends Base {
	getIndex() {
		return 1;
	}

	getType() {
		return 'content';
	}

	getIcon() {
		return 'eicon-post-content';
	}

	getTitle() {
		return __( 'Content', 'elementor' );
	}

	getConditions( document ) {
		return document.getSettings( 'id' ) === elementor.config.initial_document.id;
	}
}

export default Content;
