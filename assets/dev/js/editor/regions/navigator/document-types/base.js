export default class Base {
	getIndex() {
		// Means just insert.
		return null;
	}

	getType() {
		elementorModules.ForceMethodImplementation();
	}

	getIcon() {
		elementorModules.ForceMethodImplementation();
	}

	getTitle() {
		elementorModules.ForceMethodImplementation();
	}

	/**
	 * Function getConditions().
	 *
	 * Get the condition for the document to be recognized.
	 *
	 * @param {elementorFrontend.Document} document ( frontend document ).
	 *
	 * @returns {boolean}
	 */
	getConditions( document ) { // eslint-disable-line no-unused-vars
		elementorModules.ForceMethodImplementation();
	}
}

