import * as commands from './commands';
import * as dataCommands from './commands-data';

export default class Component extends $e.modules.ComponentBase {
	/**
	 * @inheritDoc
	 */
	__construct( args = {} ) {
		super.__construct( args );

		elementor.on( 'panel:init', () => {
			this.loadFavoriteWidgetsList();
		} );
	}

	/**
	 * @inheritDoc
	 */
	getNamespace() {
		return 'favorites';
	}

	/**
	 * @inheritDoc
	 */
	defaultCommands() {
		return this.importCommands( commands );
	}

	/**
	 * @inheritDoc
	 */
	defaultData() {
		return this.importCommands( dataCommands );
	}

	/**
	 * Retrieve stored favorite widgets using the favorites data command.
	 *
	 * @returns {Promise<*>}
	 */
	async loadFavoriteWidgetsList() {
		const favorites = $e.data.get(
			this.getFavoritesDataCommand(),
			{ key: 'widgets' },
			{ refresh: true }
		);

		return favorites.then( ( result ) => {
			$e.run( this.getCreateCommand(), { widgets: result.data, store: false } );
		} );
	}

	/**
	 * Get the favorites data command name.
	 *
	 * @returns {string}
	 */
	getFavoritesDataCommand() {
		return this.getNamespace() + '/index';
	}

	/**
	 * Get the favorites create command name.
	 *
	 * @returns {string}
	 */
	getCreateCommand() {
		return this.getNamespace() + '/create';
	}

	/**
	 * Get the favorites delete command name.
	 *
	 * @returns {string}
	 */
	getDeleteCommand() {
		return this.getNamespace() + '/delete';
	}
}
