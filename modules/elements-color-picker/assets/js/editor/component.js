import ComponentBase from 'elementor-api/modules/component-base';
import * as commands from './commands/';

export default class Component extends ComponentBase {
	currentPicker = {
		container: null,
		control: null,
		initialColor: null,
	};

	lightboxTriggers = [];

	getNamespace() {
		return 'elements-color-picker';
	}

	defaultCommands() {
		return this.importCommands( commands );
	}

	defaultShortcuts() {
		return {
			end: {
				keys: 'esc',
				scopes: [ 'panel', 'preview' ],
			},
		};
	}
}
