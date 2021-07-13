export default class ChildrenCollection extends Array {
	clear() {
		this.length = 0;
	}

	/**
	 * Function findRecursive().
	 *
	 * Will run over children recursively and pass the children to the callback till the callback returns positive value.
	 *
	 * @param {function(container:Container)} callback
	 *
	 * @returns {Container|false}
	 */
	findRecursive( callback ) {
		for ( const container of this ) {
			if ( callback( container ) ) {
				return container;
			}

			if ( container.children.length ) {
				const foundChildren = container.children.findRecursive( callback );

				if ( foundChildren ) {
					return foundChildren;
				}
			}
		}

		return false;
	}

	/**
	 * Function forEachRecursive().
	 *
	 * Will run over children recursively.
	 *
	 * @param {function(container:Container)} callback
	 *
	 * @returns {void}
	 */
	forEachRecursive( callback ) {
		for ( const container of this ) {
			callback( container );

			if ( container.children.length ) {
				container.children.forEachRecursive( callback );
			}
		}
	}

	/**
	 * Function someRecursive().
	 *
	 * Will run over children recursively, breaks if the callback return true.
	 *
	 * @param {function(container:Container)} callback
	 *
	 */
	someRecursive( callback ) {
		for ( const container of this ) {
			if ( callback( container ) ) {
				return true;
			}

			if ( container.children?.length ) {
				if ( container.children.someRecursive( callback ) ) {
					return true;
				}
			}
		}

		return false;
	}
}
