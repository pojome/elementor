/**
 * Add custom namespaced event using ES6. Equivalent to jQuery's `.on()`.
 * NOTE: Might cause memory leaks if the element is removed from then DOM without removing its `nsEvents`.
 *
 * @param {HTMLElement|NodeList} elements - An HTML element to attach the event to.
 * @param {string} nsEvent - Namespaced event name, e.g. `click.color-picker`.
 * @param {function} callback - Callback handler to the attached event.
 * @param {object} options - Additional event options.
 */
export const addNamespaceHandler = ( elements, nsEvent, callback, options = {} ) => {
	const [ event ] = nsEvent.split( '.' );

	// Add support for multi elements.
	if ( ! ( Symbol.iterator in Object( elements ) ) ) {
		elements = [ elements ];
	}

	elements.forEach( ( element ) => {
		if ( ! element.nsEvents ) {
			element.nsEvents = {};
		}

		element.addEventListener( event, callback, options );
		element.nsEvents[ nsEvent ] = callback;
	} );
};

/**
 * Remove custom namespaced event using ES6. Equivalent to jQuery's `.off()`.
 *
 * @param {NodeList} elements - An HTML element to remove the event from.
 * @param {string} nsEvent - Namespaced event name, e.g. `click.color-picker`.
 */
export const removeNamespaceHandler = ( elements, nsEvent ) => {
	const [ event ] = nsEvent.split( '.' );

	// Add support for multi elements.
	if ( ! ( Symbol.iterator in Object( elements ) ) ) {
		elements = [ elements ];
	}

	elements.forEach( ( element ) => {
		element.removeEventListener( event, element.nsEvents?.[ nsEvent ] );
		delete element.nsEvents?.[ nsEvent ];
	} );
};
