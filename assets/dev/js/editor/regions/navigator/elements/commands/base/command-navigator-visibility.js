import CommandNavigator from './command-navigator';

export class CommandNavigatorVisibility extends CommandNavigator {
	/**
	 * Function shouldHide().
	 *
	 * @returns {Boolean}
	 */
	shouldHide() {
		elementorModules.ForceMethodImplementation();
	}

	apply( args ) {
		const { containers = [ args.container ] } = args;

		containers.forEach( ( container ) => {
			const { view, model } = container.navigator;

			model.set( 'hidden', this.shouldHide() );

			view.toggleHiddenClass();
		} );
	}
}

export default CommandNavigatorVisibility;
