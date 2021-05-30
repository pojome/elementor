import Base from './base';

export class Header extends Base {
	getIndex() {
		return 0;
	}

	getType() {
		return 'header';
	}

	getIcon() {
		return 'eicon-header';
	}

	getTitle() {
		return __( 'Header', 'elementor' );
	}

	getConditions( document ) {
		return document.$element.hasClass( 'elementor-location-header' );
	}
}

export default Header;
