import BaseHandler from './base-handler';
import { parseType } from '../utils';

export default class LocalValues extends BaseHandler {
	appendSettingsForSave( settings, container ) {
		const controls = container.settings.controls;
		const settingsWithoutDefaults = container.settings.toJSON( { remove: [ 'hard-coded-default' ] } );

		const localSettings = Object.entries( settingsWithoutDefaults ).filter(
			( [ controlName ] ) => controls[ controlName ] && container.view.isStyleTransferControl( controls[ controlName ] )
		);

		return {
			...settings,
			...Object.fromEntries( localSettings ),
		};
	}

	appendSettingsForRecreate( element ) {
		return element;
	}

	registerHooks() {
		elementor.hooks.addFilter( 'editor/controls/base/default-value', ( defaultValue, control, settings ) => {
			const type = parseType( settings );

			if ( ! type ) {
				return defaultValue;
			}

			const dynamicDefaults = $e.data.getCache(
				$e.components.get( 'default-values' ),
				`default-values/${ type }`
			);

			if ( ! dynamicDefaults || ! dynamicDefaults[ control.name ] ) {
				return defaultValue;
			}

			return dynamicDefaults[ control.name ];
		} );
	}
}
