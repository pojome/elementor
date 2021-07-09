import CommandEditorInternal from 'elementor-editor/command-bases/command-editor-internal';

export class SetSettings extends CommandEditorInternal {
	validateArgs( args = {} ) {
		this.requireContainer( args );
		this.requireArgumentType( 'settings', 'object', args );

		if ( 'undefined' !== typeof args.render && 'undefined' !== typeof args.renderUI ) {
			throw new Error( 'Args: `render` and `renderUI` cannot be applied together.' );
		}
	}

	apply( args = {} ) {
		const { containers = [ args.container ], settings, options = {} } = args,
			{ external, render = true, renderUI = false } = options;

		containers.forEach( ( container ) => {
			if ( external ) {
				container.settings.setExternalChange( settings );
			} else {
				container.settings.set( settings );
			}

			if ( renderUI ) {
				container.renderUI();
			} else if ( render ) {
				container.render();
			}
		} );
	}
}

export default SetSettings;
