import ComponentBase from 'elementor-api/modules/component-base';
import ElementsComponent from './elements/component';

import * as commands from './commands/';
import * as hooks from './hooks/index';

export default class Component extends ComponentBase {
	__construct( args ) {
		super.__construct( args );

		this.elements = $e.components.register( new ElementsComponent() );
	}

	getNamespace() {
		return 'navigator';
	}

	defaultRoutes() {
		return {
			'': () => {},
		};
	}

	defaultCommands() {
		return this.importCommands( commands );
	}

	defaultShortcuts() {
		return {
			toggle: {
				keys: 'ctrl+i',
				dependency: () => elementor.getPreviewContainer().isEditable(),
			},
		};
	}

	defaultHooks() {
		return this.importHooks( hooks );
	}

	open( args ) {
		const { model = false } = args;

		this.manager.open( model );

		return true;
	}

	close( silent ) {
		if ( ! super.close() ) {
			return false;
		}

		this.manager.close( silent );

		return true;
	}
}
