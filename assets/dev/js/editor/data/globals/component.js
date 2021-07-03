import ComponentBase from 'elementor-api/modules/component-base';
import TypographyComponent from './typography/component';
import ColorsComponent from './colors/component';

export default class Component extends ComponentBase {
	__construct( args = {} ) {
		super.__construct( args );

		elementorCommon.elements.$window.on( 'elementor:loaded', () => this.refreshGlobalData() );
	}

	getNamespace() {
		return 'globals';
	}

	registerAPI() {
		$e.components.register( new TypographyComponent( { manager: this } ) );
		$e.components.register( new ColorsComponent( { manager: this } ) );

		super.registerAPI();
	}

	defaultData() {
		return {
			index: 'globals',
			colors: 'globals/colors/{id}',
			typography: 'globals/typography/{id}',
		};
	}

	refreshGlobalData() {
		$e.data.deleteCache( $e.components.get( 'globals' ), 'globals/index' );
	}
}
