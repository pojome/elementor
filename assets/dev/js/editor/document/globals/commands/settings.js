import CommandHistoryDebounce from 'elementor-document/commands/base/command-history-debounce';
import ElementsSettings from 'elementor-document/elements/commands/settings';

export class Settings extends CommandHistoryDebounce {
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
		const { settings, containers = [ args.container ], options = {} } = args,
			{ external, render = true } = options;

		containers.forEach( ( container ) => {
			container = container.lookup();

			if ( ! Object.keys( settings ).length ) {
				container.globals.clear();
			} else {
				container.globals.set( settings );
			}

			const globalSettings = container.globals.toJSON();

			if ( external ) {
				container.settings.setExternalChange( '__globals__', globalSettings );
			} else {
				container.settings.set( '__globals__', globalSettings );
			}

			if ( render ) {
				container.render();
			}
		} );
	}

	isDataChanged() {
		return true;
	}
}

export default Settings;
