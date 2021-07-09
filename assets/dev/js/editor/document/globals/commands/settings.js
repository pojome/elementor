import CommandHistoryDebounceBase from 'elementor-document/command-bases/command-history-debounce-base';
import ElementsSettings from 'elementor-document/elements/commands/settings';

export class Settings extends CommandHistoryDebounceBase {
	static restore( historyItem, isRedo ) {
		const data = historyItem.get( 'data' );

		historyItem.get( 'containers' ).forEach( ( container ) => {
			const changes = data.changes[ container.id ];

			$e.run( 'document/globals/settings', {
				container,
				settings: isRedo ? changes.new : changes.old,
			} );

			container.panel.refresh();
		} );
	}

	validateArgs( args ) {
		this.requireContainer( args );

		this.requireArgumentConstructor( 'settings', Object, args );
	}

	getHistory( args ) {
		const { settings, containers = [ args.container ] } = args,
			changes = {};

		containers.forEach( ( container ) => {
			const { id } = container;

			if ( ! changes[ id ] ) {
				changes[ id ] = {};
			}

			changes[ id ] = {
				old: container.globals.toJSON(),
				new: settings,
			};
		} );

		const subTitle = ElementsSettings.getSubTitle( args );

		return {
			containers,
			subTitle,
			data: { changes },
			type: 'change',
			restore: this.constructor.restore,
		};
	}

	apply( args ) {
		const { settings, containers = [ args.container ], options = {} } = args;

		containers.forEach( ( container ) => {
			container = container.lookup();

			if ( ! Object.keys( settings ).length ) {
				container.globals.clear();
			} else {
				container.globals.set( settings );
			}

			$e.internal( 'document/elements/set-settings', {
				container,
				options,
				settings: {
					__globals__: container.globals.toJSON(),
				},
			} );
		} );
	}
}

export default Settings;
