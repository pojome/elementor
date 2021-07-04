import CommandBase from 'elementor-api/modules/command-base';

export class Close extends CommandBase {
	apply() {
		this.component.close();
	}
}

export default Close;
