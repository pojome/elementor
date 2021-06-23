import * as commandsData from './commands-data/';

export default class Component extends $e.modules.ComponentBase {
	getNamespace() {
		return 'kit-taxonomies';
	}

	defaultData() {
		return this.importCommands( commandsData );
	}
}
