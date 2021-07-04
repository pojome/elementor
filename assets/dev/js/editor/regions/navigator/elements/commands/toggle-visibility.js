import CommandNavigator from './base/command-navigator';

export class ToggleVisibility extends CommandNavigator {
	apply( args ) {
		const { containers = [ args.container ] } = args;

		containers.forEach( ( container ) => {
			const { model } = container.navigator;

			if ( model.get( 'hidden' ) ) {
				$e.run( 'navigator/elements/show', { container } );
			} else {
				$e.run( 'navigator/elements/hide', { container } );
			}
		} );
	}
}

export default ToggleVisibility;
