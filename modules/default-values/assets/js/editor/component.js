import * as commands from './commands/index';
import * as dataCommands from './commands-data/index';
import GlobalValues from './handlers/global-values';
import LocalValues from './handlers/local-values';

export default class Component extends $e.modules.ComponentBase {
	getNamespace() {
		return 'default-values';
	}

	defaultCommands() {
		return this.importCommands( commands );
	}

	defaultData() {
		return this.importCommands( dataCommands );
	}

	__construct( args = {} ) {
		/**
		 * Handlers responsible for the different strategies to manipulate and getting the settings
		 * from local values or globals
		 *
		 * @type {BaseHandler[]} BaseHandler path: './handlers/base-handler'
		 */
		this.handlers = [
			new LocalValues(), // Must be first to allow the globals change the settings data.
			new GlobalValues(),
		];

		super.__construct( args );

		elementor.hooks.addFilter( 'editor/before-on-start', ( callbacks ) => [
			...callbacks,
			() => {
				$e.data.deleteCache( this, 'default-values/index' );

				return $e.data.get( 'default-values/index', {}, {} );
			},
		] );

		this.handlers.forEach( ( handler ) => handler.registerHooks() );
	}
}
