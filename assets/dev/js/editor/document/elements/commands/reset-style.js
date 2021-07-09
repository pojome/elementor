import CommandHistoryBase from '../../command-bases/command-history-base';

export class ResetStyle extends CommandHistoryBase {
	validateArgs( args ) {
		this.requireContainer( args );
	}

	getHistory( args ) {
		const { containers = [ args.container ] } = args;

		return {
			containers,
			type: 'reset_style',
		};
	}

	apply( args ) {
		const { containers = [ args.container ] } = args;

		containers.forEach( ( container ) => {
			const controls = container.settings.controls,
				settingsKeys = [];

			container.view.allowRender = false;

			Object.entries( controls ).forEach( ( [ controlName, control ] ) => {
				if ( ! container.view.isStyleTransferControl( control ) ) {
					return;
				}

				settingsKeys.push( controlName );
			} );

			// BC: Deprecated since 2.8.0 - use `$e.hooks`.
			elementor.channels.data.trigger( 'element:before:reset:style', container.model );

			$e.run( 'document/elements/reset-settings', {
				container,
				settings: settingsKeys,
				options: {
					external: true,
				},
			} );

			// BC: Deprecated since 2.8.0 - use `$e.hooks`.
			elementor.channels.data.trigger( 'element:after:reset:style', container.model );

			container.view.allowRender = true;

			container.render();
		} );
	}
}

export default ResetStyle;
