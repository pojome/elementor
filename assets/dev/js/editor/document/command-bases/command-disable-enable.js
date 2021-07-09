import CommandHistoryBase from './command-history-base';
import ElementsSettings from 'elementor-document/elements/commands/settings';

export default class CommandDisableEnable extends CommandHistoryBase {
	static getName() {
		elementorModules.ForceMethodImplementation();
	}

	/**
	 * @returns {string}
	 */
	static getEnableCommand() {
		elementorModules.ForceMethodImplementation();
	}

	/**
	 * @returns {string}
	 */
	static getDisableCommand() {
		elementorModules.ForceMethodImplementation();
	}

	static restore( historyItem, isRedo ) {
		const data = historyItem.get( 'data' ),
			CommandClass = $e.commands.getCommandClass( data.command );

		// Upon `disable` command toggle `isRedo`.
		if ( CommandClass.getDisableCommand() === data.command ) {
			isRedo = ! isRedo;
		}

		historyItem.get( 'containers' ).forEach( ( container ) => {
			const settings = data.changes[ container.id ],
				toggle = isRedo ? CommandClass.getEnableCommand() : CommandClass.getDisableCommand();

			$e.run( toggle, {
				container,
				settings,
			} );

			container.panel.refresh();
		} );
	}

	initialize( args ) {
		/**
		 * Which command is running.
		 *
		 * @type {string}
		 */
		this.type = this.command === this.constructor.getEnableCommand() ?
			'enable' : 'disable';

		// Override default logic, since getHistory() depends on `this.type`.
		super.initialize( args );
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

			changes[ id ] = settings;
		} );

		const subTitle = elementor.translate( this.constructor.getName() ) + ' ' + ElementsSettings.getSubTitle( args ),
			type = this.type;

		return {
			containers,
			subTitle,
			data: {
				changes,
				command: this.command,
			},
			type,
			restore: this.constructor.restore,
		};
	}
}
