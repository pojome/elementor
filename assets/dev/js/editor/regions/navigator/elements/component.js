import ComponentBase from 'elementor-api/modules/component-base';
import * as commands from './commands/';
import * as hooks from './hooks/';

export default class Component extends ComponentBase {
	getNamespace() {
		return 'navigator/elements';
	}

	defaultCommands() {
		return this.importCommands( commands );
	}

	defaultHooks() {
		return this.importHooks( hooks );
	}
}
