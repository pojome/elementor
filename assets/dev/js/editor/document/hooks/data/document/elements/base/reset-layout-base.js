import After from 'elementor-api/modules/hooks/data/after';

export default class ResetLayoutBase extends After {
	getConditions() {
		// On `document/elements/move` do not fire the hook!.
		return ! $e.commands.isCurrentFirstTrace( 'document/elements/move' );
	}

	/**
	 * @inheritDoc
	 *
	 * @param {{}} args
	 * @param {Container||Container[]} containers
	 *
	 * @returns {boolean}
	 */
	apply( args, containers ) {
		if ( ! Array.isArray( containers ) ) {
			containers = [ containers ];
		}

		containers.forEach( ( /**Container*/ container ) =>
			container.parent.view.resetLayout( false )
		);
	}
}
