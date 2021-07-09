import CommandDisableEnable from 'elementor-document/command-bases/command-disable-enable';

export default class DisableEnable extends CommandDisableEnable {
	static getName() {
		return 'Global';
	}

	getTitle() {
		return __( 'Global' );
	}

	static getEnableCommand() {
		return 'document/globals/enable';
	}

	static getDisableCommand() {
		return 'document/globals/disable';
	}
}
