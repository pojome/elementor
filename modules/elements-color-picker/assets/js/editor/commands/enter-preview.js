import CommandBase from 'elementor-api/modules/command-base';

export class EnterPreview extends CommandBase {
	apply( args ) {
		// Silent
		this.component.currentPicker.container.settings.set( this.component.currentPicker.control, args.value );
		this.component.currentPicker.container.view.renderUI();
	}
}
