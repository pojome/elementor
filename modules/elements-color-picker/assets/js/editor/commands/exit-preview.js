import CommandBase from 'elementor-api/modules/command-base';

export class ExitPreview extends CommandBase {
	apply( args ) {
		const { initialColor } = this.component.currentPicker;

		if ( null === initialColor ) {
			return;
		}

		this.component.renderUI( initialColor );
	}
}
