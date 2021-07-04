import CommandNavigator from './base/command-navigator';

export class Expand extends CommandNavigator {
	apply( args ) {
		const { containers = [ args.container ], callback } = args;

		containers.forEach( ( container ) => {
			const view = container.navigator.view;

			// TODO: move to UI HOOK.
			view.ui.item.toggleClass( 'elementor-active', true );

			view.ui.elements.slideDown( 300, callback );
		} );
	}
}

export default Expand;
