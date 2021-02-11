import CommandBase from 'elementor-api/modules/command-base';

export class Exit extends CommandBase {
	apply() {
		this.component.$favoriteMenu.remove();
	}
}

export default Exit;
