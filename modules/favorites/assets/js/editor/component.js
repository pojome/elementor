import * as commands from './commands';
import * as dataCommands from './commands-data';

export default class Component extends $e.modules.ComponentBase {
	/**
	 * @inheritDoc
	 */
	getNamespace() {
		return 'favorites';
	}

	/**
	 * @inheritDoc
	 */
	defaultCommands() {
		return this.importCommands( commands );
	}

	/**
	 * @inheritDoc
	 */
	defaultData() {
		return this.importCommands( dataCommands );
	}
}
