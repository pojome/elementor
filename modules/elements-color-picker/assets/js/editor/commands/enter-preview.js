import CommandBase from 'elementor-api/modules/command-base';

export class EnterPreview extends CommandBase {
	apply( args ) {
		this.component.renderUI( args.value );
	}
}
