import After from 'elementor-api/modules/hooks/data/after';

export class CreateColumnForEmptySection extends After {
	getCommand() {
		return 'document/elements/delete';
	}

	getId() {
		return 'create-column-for-empty-section--document/elements/delete';
	}

	getContainerType() {
		return 'column';
	}

	getConditions( args = {} ) {
		const { containers = [ args.container ] } = args;

		// Validate also that its a section, this is hook should not work with new flex container.
		return containers.some(
			( container ) => 'section' === container.parent.type && 0 === container.parent.children.length
		);
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

		containers.forEach( ( /**Container*/ container ) => {
			const parent = container.parent;

			// If deleted the last column, should recreate it.
			if ( 0 === parent.children.length ) {
				$e.run( 'document/elements/create', {
					container: parent,
					model: {
						elType: 'column',
					},
				} );
			}
		} );
	}
}

export default CreateColumnForEmptySection;

