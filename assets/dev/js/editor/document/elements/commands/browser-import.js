import CommandBase from 'elementor-api/modules/command-base';
import BrowserImportManager from '../../../utils/browser-import/manager';

export class BrowserImport extends CommandBase {
	validateArgs( args ) {
		this.requireContainer( args );
	}

	apply( args ) {
		const { containers = [ args.container ], items, options = {} } = args;

		containers.map( ( container ) => {
			new BrowserImportManager( container, options )
				.import( items )
				.then( ( manager ) => {
					manager.render();
				} );
		} );
	}
}

export default BrowserImport;
