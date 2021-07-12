import BaseHandler from './base-handler';

export default class GlobalValues extends BaseHandler {
	appendSettingsForSave( settings, container ) {
		const type = container.model.attributes.widgetType;

		if ( ! type ) {
			// No support for 'section' and 'column' (for globals)
			return settings;
		}

		const widgetControls = elementor.widgetsCache[ type ].controls;
		const settingsKeys = Object.keys( settings );

		const globalSettings = Object.fromEntries(
				Object.entries( container.settings.attributes?.__globals__ || {} )
					.filter( ( [ , value ] ) => value )
		);

		const widgetGlobalDefaults = Object.fromEntries(
			Object.entries( widgetControls )
				.filter( ( [ , control ] ) => control.global?.default )
				.filter( ( [ key ] ) => ! settingsKeys.includes( key ) ) // Removes all the values that override by local values
				.map( ( [ key, control ] ) => [ key, control.global.default ] )
		);

		if (
			! Object.keys( widgetGlobalDefaults ).length &&
			! Object.keys( globalSettings ).length
		) {
			return settings;
		}

		return {
			...settings,
			__globals__: {
				...widgetGlobalDefaults,
				...globalSettings,
			},
		};
	}

	appendSettingsForRecreate( element, newDefaultSettings ) {
		if ( ! newDefaultSettings.__globals__ || ! element.settings.__globals__ ) {
			return element;
		}

		const newDefaultSettingsKeys = Object.keys( newDefaultSettings.__globals__ );

		element.settings.__globals__ = Object.fromEntries(
			Object.entries( element.settings.__globals__ )
				.filter( ( [ key ] ) => ! newDefaultSettingsKeys.includes( key )
			)
		);

		return element;
	}

	afterSaved( type, settings ) {
		if ( ! settings.__globals__ ) {
			return;
		}

		this.assignGlobalsDefaultsToWidgetCache( type, settings.__globals__ );

		elementor.kitManager.renderGlobalsDefaultCSS();
	}

	registerHooks() {
		elementor.hooks.addFilter( 'editor/widgets-cache', ( widgets ) => {
			const dynamicDefaultValues = $e.data.getCache(
				$e.components.get( 'default-values' ),
				`default-values`
			);

			Object.entries( dynamicDefaultValues )
				.filter( ( [ widgetKey, controlsValues ] ) => controlsValues.__globals__ && widgets[ widgetKey ] )
				.forEach( ( [ widgetKey, controlsValues ] ) => {
					Object.entries( controlsValues.__globals__ )
						.filter( ( [ controlKey ] ) => widgets[ widgetKey ].controls[ controlKey ] )
						.forEach( ( [ controlKey, value ] ) =>
							// Assign the default global for specific widget and specific control
							widgets[ widgetKey ].controls[ controlKey ].global.default = value
						);
				} );

			return widgets;
		} );
	}

	/**
	 * Update widget cache to the new global defaults.
	 *
	 * @param type
	 * @param settings
	 */
	assignGlobalsDefaultsToWidgetCache( type, settings ) {
		if ( ! elementor.widgetsCache[ type ] ) {
			return;
		}

		Object.entries( settings ).forEach( ( [ key, value ] ) => {
			if ( ! elementor.widgetsCache[ type ].controls[ key ] ) {
				return;
			}

			elementor.widgetsCache[ type ].controls[ key ].global.default = value;
		} );
	}
}
