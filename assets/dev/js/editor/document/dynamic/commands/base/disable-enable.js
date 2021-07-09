import CommandDisableEnable from 'elementor-document/command-bases/command-disable-enable';

export default class DisableEnable extends CommandDisableEnable {
	static getName() {
		return 'Dynamic';
	}

	static getEnableCommand() {
		return 'document/dynamic/enable';
	}

	static getDisableCommand() {
		return 'document/dynamic/disable';
	}

	getTitle() {
		return __( 'Dynamic' );
	}
}
