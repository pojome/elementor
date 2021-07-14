import After from 'elementor-api/modules/hooks/data/after';

/**
 * Hook to set `flex-direction: column` on nested Container elements.
 */
export class NestedContainerDirection extends After {
	getCommand() {
		return 'document/elements/create';
	}

	getId() {
		return 'nested-container-direction--document/elements/create';
	}

	getConditions( args ) {
		const { containers = [ args.container ], model } = args;

		// Execute only for Containers which are children of another element.
		return 'container' === model.elType && containers.some( ( /**Container*/ container ) => container.model.get( 'elType' ) !== 'document' );
	}

	apply( args, containers ) {
		if ( ! Array.isArray( containers ) ) {
			containers = [ containers ];
		}

		// Set direction column as default.
		containers.forEach( ( container ) => {
			$e.run( 'document/elements/settings', {
				container,
				settings: {
					container_flex_direction: 'column',
				},
				options: {
					external: true,
				},
			} );
		} );
	}
}

export default NestedContainerDirection;
