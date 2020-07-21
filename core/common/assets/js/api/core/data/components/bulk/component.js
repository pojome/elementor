import ComponentBase from 'elementor-api/modules/component-base';

import * as commandsData from './commands/data/';

export default class Component extends ComponentBase {
	getNamespace() {
		return 'bulk';
	}

	defaultData() {
		return this.importCommands( commandsData );
	}
}
