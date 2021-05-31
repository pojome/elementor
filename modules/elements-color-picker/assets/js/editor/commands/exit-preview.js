import CommandBase from 'elementor-api/modules/command-base';

export class ExitPreview extends CommandBase {
	apply( args ) {
		if ( null === this.component.currentPicker.initialColor ) {
			return;
		}

		// Silent
		this.component.currentPicker.container.settings.set( this.component.currentPicker.control, this.component.currentPicker.initialColor );
		this.component.currentPicker.container.view.renderUI();
	}
}
