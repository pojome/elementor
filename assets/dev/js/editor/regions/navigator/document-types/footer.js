import Base from './base';

export class Footer extends Base {
	getIndex() {
		return 2;
	}

	getType() {
		return 'footer';
	}

	getIcon() {
		return 'eicon-footer';
	}

	getTitle() {
		return __( 'Footer', 'elementor' );
	}

	getConditions( document ) {
		return document.$element.hasClass( 'elementor-location-footer' );
	}
}

export default Footer;
