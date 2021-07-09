import ComponentBase from 'elementor-api/modules/component-base';
import BackwardsCompatibility from './backwards-compatibility.js';
import CommandHistoryBase from '../document/command-bases/command-history-base';
import CommandHistoryDebounceBase from '../document/command-bases/command-history-debounce-base';

import * as components from './';
import * as hooks from './hooks/';

export default class Component extends ComponentBase {
	static getModules() {
		return {
			CommandHistoryBase,
			CommandHistoryDebounceBase,
		};
	}

	getNamespace() {
		return 'document';
	}

	registerAPI() {
		new BackwardsCompatibility();

		Object.values( components ).forEach( ( ComponentClass ) =>
			$e.components.register( new ComponentClass )
		);

		super.registerAPI();
	}

	defaultCommands() {
		return {
			//example: ( args ) => ( new Commands.Example( args ).run() ),
		};
	}

	defaultHooks() {
		return this.importHooks( hooks );
	}

	defaultUtils() {
		return {
			findViewRecursive: ( parent, key, value, multiple = true ) => {
				let found = [];
				for ( const x in parent._views ) {
					const view = parent._views[ x ];

					if ( value === view.model.get( key ) ) {
						found.push( view );
						if ( ! multiple ) {
							return found;
						}
					}

					if ( view.children ) {
						const views = this.utils.findViewRecursive( view.children, key, value, multiple );
						if ( views.length ) {
							found = found.concat( views );
							if ( ! multiple ) {
								return found;
							}
						}
					}
				}

				return found;
			},
			findViewById: ( id ) => {
				const elements = this.utils.findViewRecursive(
					elementor.getPreviewView().children,
					'id',
					id,
					false
				);

				return elements ? elements[ 0 ] : false;
			},
			findContainerById: ( id ) => {
				let result = this.utils.findViewById( id );

				if ( result ) {
					result = result.getContainer();
				}

				return result;
			},
		};
	}
}
