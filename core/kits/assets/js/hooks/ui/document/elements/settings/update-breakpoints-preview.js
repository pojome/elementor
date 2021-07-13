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

		// If the 'active_breakpoints' control was changed, we need to make sure that all of the breakpoints in the new
		// setting are now set to active.
		if ( settings.active_breakpoints ) {
			// Clear the active breakpoints object before repopulating it, to make sure unselected breakpoints are removed.
			elementorFrontend.config.responsive.activeBreakpoints = {};

			settings.active_breakpoints.forEach( ( breakpointName ) => {
				breakpointName = breakpointName.replace( 'viewport_', '' );
				// Set its state to enabled.
				elementorFrontend.config.responsive.breakpoints[ breakpointName ].is_enabled = true;
				// Add/re-add the breakpoint to the emptied activeBreakpoints object.
				elementorFrontend.config.responsive.activeBreakpoints[ breakpointName ] = elementorFrontend.config.responsive.breakpoints[ breakpointName ];
			} );

			elementor.activeBreakpointsUpdated = true;

			// If this is the modified setting, no need to do further checks.
			return;
		}

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
