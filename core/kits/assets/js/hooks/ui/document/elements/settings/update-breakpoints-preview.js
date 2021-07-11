export class KitUpdateBreakpointsPreview extends $e.modules.hookUI.After {
	getCommand() {
		return 'document/elements/settings';
	}

	getId() {
		return 'kit-update-breakpoints-preview';
	}

	getContainerType() {
		return 'document';
	}

	getConditions() {
		return 'kit' === elementor.documents.getCurrent().config.type;
	}

	apply( args ) {
		const { settings } = args;

		// If a breakpoint value was updated, update the value in the config.
		Object.entries( settings ).forEach( ( [ key, value ] ) => {
			if ( key.startsWith( 'viewport_' ) ) {
				const keyWithoutPrefix = key.replace( 'viewport_', '' );
				// Update both the config for all breakpoints and the one for active breakpoints.

				if ( ! value ) {
					value = elementorFrontend.config.responsive.breakpoints[ keyWithoutPrefix ].default_value;
				}

				elementorFrontend.config.responsive.breakpoints[ keyWithoutPrefix ].value = value;
			}
		} );

		elementor.updatePreviewResizeOptions( true );
	}
}
