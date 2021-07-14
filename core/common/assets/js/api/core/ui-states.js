export default class UiStates {
	/**
	 * Initialize the State Manager.
	 *
	 * @return {void}
	 */
	constructor() {
		this.states = {};
	}

	/**
	 * Register a new state.
	 *
	 * @param {UiStateBase} instance - State instance.
	 * @param {Document[]|HTMLElement[]} context - List of contexts for the state. Defaults to current document.
	 *
	 * @return {void}
	 */
	register( instance, context = [ window.document ] ) {
		const stateId = instance.getPrefixedId();

		if ( this.states[ stateId ] ) {
			throw `State '${ stateId }' already exists.`;
		}

		this.states[ stateId ] = {
			instance,
			context,
		};
	}

	/**
	 * Get all existing states with their options:
	 *
	 * {
	 *     'state-id': [
	 *         'option-1',
	 *         'option-2',
	 *         'option-3',
	 *     ],
	 * }
	 *
	 * @return {Object}
	 */
	getAll() {
		const states = {};

		Object.entries( this.states ).forEach( ( [ id, { instance } ] ) => {
			const options = instance.getOptions();

			states[ id ] = Object.keys( options );
		} );

		return states;
	}

	/**
	 * Get the state value, or return all of them if no `state` is set.
	 *
	 * @param {string} state - State ID.
	 *
	 * @return {object}
	 */
	get( state ) {
		if ( state ) {
			return this.states[ state ];
		}

		return this.states;
	}

	/**
	 * Set the current state value and trigger its callbacks & events.
	 * This function triggers a `state:${ stateID }` event to the context, with `oldValue` & `newValue`
	 * under `e.detail`.
	 * Additionally, it adds a `e-ui-state--${ stateID }__${ value }` class to the context's body.
	 *
	 * @param {string} state - State ID.
	 * @param {string} value - New state value.
	 *
	 * @return {void}
	 */
	set( state, value ) {
		// Invalid state or option.
		if ( ! this.get( state ) ) {
			throw `State '${ state }' doesn't exist.`;
		}

		const oldValue = this.getCurrent( state ),
			classPrefix = `e-ui-state--${ state.replaceAll( '/', '-' ) }`,
			oldStateClass = `${ classPrefix }__${ oldValue }`,
			newStateClass = `${ classPrefix }__${ value }`,
			context = this.states[ state ].context;

		// Set the current state to the new value.
		this.get( state ).instance.set( value );

		context.forEach( ( c ) => {
			// if it's a document instance, get the body element as a context.
			const contextElement = ( c instanceof HTMLDocument ) ? c.body : c;

			contextElement.classList.remove( oldStateClass );

			// Set the new class only if there is a value (i.e. it's not a state removal action).
			if ( value ) {
				contextElement.classList.add( newStateClass );
			}

			// Dispatch a custom state-change event to the context.
			const event = new CustomEvent( `state:${ state }`, {
				detail: {
					oldValue,
					newValue: value,
				},
			} );

			c.dispatchEvent( event );
		} );
	}

	/**
	 * Remove a state.
	 *
	 * @param {string} state - State ID.
	 *
	 * @return {void}
	 */
	remove( state ) {
		this.set( state, '' );
	}

	/**
	 * Get the current state value, or return all of them if no `state` is set.
	 *
	 * @param {string} state - State ID.
	 *
	 * @return {string}
	 */
	getCurrent( state ) {
		const { instance } = this.get( state );

		return instance?.getCurrent();
	}
}
