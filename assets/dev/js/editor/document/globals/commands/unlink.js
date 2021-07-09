import CommandEditorBase from 'elementor-editor/command-bases/command-editor-base';

// TODO: Add dev-tools CSS to see if widget have globals.
export class Unlink extends CommandEditorBase {
	validateArgs( args = {} ) {
		this.requireContainer( args );
		this.requireArgumentType( 'setting', 'string', args );
		this.requireArgumentType( 'globalValue', 'string', args );

		// TODO: validate global value is command format.
	}

	async apply( args ) {
		const { containers = [ args.container ], setting, globalValue, options = {} } = args,
			localSettings = {};

		await Promise.all( containers.map( async ( /* Container */ container ) => {
			const result = await $e.data.get( globalValue );

			if ( result ) {
				// Prepare global value to mapping.
				const { value } = result.data,
					groupPrefix = container.controls[ setting ]?.groupPrefix;

				if ( groupPrefix ) {
					Object.entries( value ).forEach( ( [ dataKey, dataValue ] ) => {
						dataKey = dataKey.replace( elementor.config.kit_config.typography_prefix, groupPrefix );
						localSettings[ dataKey ] = dataValue;
					} );
				} else {
					localSettings[ setting ] = value;
				}
			}

			return Promise.resolve();
		} ) );

		// Restore globals settings as custom local settings.
		if ( Object.keys( localSettings ).length ) {
			$e.run( 'document/elements/settings', {
				containers,
				options,
				settings: localSettings,
			} );
		}
	}
}

export default Unlink;
