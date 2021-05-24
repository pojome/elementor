import * as dataCommands from './commands';

export default class Component extends $e.modules.ComponentBase {
	__construct( args = {} ) {
		super.__construct( args );

		elementorCommon.elements.$window.on( 'elementor:loaded', () => this.refreshGlobalData() );
	}

	getNamespace() {
		return 'favorites';
	}

	defaultData() {
		return this.importCommands( dataCommands );
	}

	refreshGlobalData() {
		$e.data.deleteCache(
			$e.components.get( this.getNamespace() ),
			this.getNamespace() + '/index'
		);
	}
}
