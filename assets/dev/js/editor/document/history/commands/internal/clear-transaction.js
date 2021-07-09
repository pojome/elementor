import CommandInternalBase from 'elementor-api/modules/command-internal-base';

export class ClearTransaction extends CommandInternalBase {
	apply() {
		this.component.transactions = [];
	}
}

export default ClearTransaction;
