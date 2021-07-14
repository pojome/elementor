export default class UiStateBase {
	/**
	 * Initialize the state object.
	 *
	 * @param {ComponentBase} component - Optional. The component that the state belongs to.
	 *
	 * @return {void}
	 */
	constructor( component ) {
		this.component = component;

		this.id = this.getId();
		this.options = this.getOptions();
		this.currentState = null;
	}

	/**
	 * Set the current state.
	 *
	 * @param {string} newValue - The new value to set as state. Has to be one of `this.options`.
	 *
	 * @return {void}
	 */
	set( newValue ) {
		if ( newValue && ! this.options.hasOwnProperty( newValue ) ) {
			throw `Option '${ newValue }' for state '${ this.id }' is invalid.`;
		}

		const callback = this.options[ newValue ],
			oldValue = this.currentState;

		// Change the current state.
		this.currentState = newValue;

		// Run the callback if it's callable.
		if ( 'function' === typeof callback ) {
			callback( oldValue, newValue );
		}
	}

	/**
	 * Get current state value.
	 *
	 * @return {string}
	 */
	getCurrent() {
		return this.currentState;
	}

	/**
	 * Get state ID.
	 *
	 * @return {string}
	 */
	getId() {
		elementorModules.forceMethodImplementation();
	}

	/**
	 * Return the state ID prefix.
	 *
	 * @return {string}
	 */
	getPrefix() {
		return this.component?.getNamespace() || '';
	}

	/**
	 * Get the prefixed state ID.
	 *
	 * @return {string}
	 */
	getPrefixedId() {
		const prefix = this.getPrefix();

		if ( ! prefix ) {
			return this.getId();
		}

		return `${ prefix }/${ this.getId() }`;
	}

	/**
	 * Get state options.
	 * Each option should have unique ID as key and a callback if needed (should be an inner class method).
	 *
	 * @return {object}
	 */
	getOptions() {
		return {
			on: '',
			off: '',
		};
	}

	/**
	 * Retrieve an array of contexts that the state will be applied to.
	 *
	 * @return {Document[]|HTMLElement[]}
	 */
	getContexts() {
		return [
			window.document,
		];
	}
}
