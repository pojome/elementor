import Module from 'elementor-common/modules/imports/module';

export default class HooksBase extends Module {
	/**
	 * Function constructor().
	 *
	 * Create hooks base.
	 *
	 * @param {{}} args
	 */
	constructor( ...args ) {
		super( ...args );

		/**
		 * Current command.
		 *
		 * @type {string}
		 */
		this.current = '';

		/**
		 * Array of ids which in use.
		 *
		 * @type {Array}
		 */
		this.usedIds = [];

		/**
		 * Object of callbacks that was bound by container type.
		 *
		 * @type {{}}
		 */
		this.callbacks = {
			after: {},
			catch: {},
		};

		/**
		 * Object of depth.
		 *
		 * @type {{}}
		 */
		this.depth = {
			after: {},
			catch: {},
		};

		this.callbacksFlatList = {};
	}

	activate() {
		Object.values( this.getAll( true ) ).forEach( ( callback ) => {
			callback.activate();
		} );
	}

	deactivate() {
		Object.values( this.getAll( true ) ).forEach( ( callback ) => {
			callback.deactivate();
		} );
	}

	/**
	 * Function getType().
	 *
	 * Returns type eg: ( event, hook, etc ... ).
	 *
	 * @returns {string} type
	 */
	getType() {
		elementorModules.forceMethodImplementation();
	}

	get( id ) {
		return this.callbacksFlatList[ id ];
	}

	/**
	 * Function getAll().
	 *
	 * Return all possible callbacks.
	 *
	 * @param {boolean} flat
	 *
	 * @returns {{}}
	 */
	getAll( flat = false ) {
		if ( flat ) {
			return this.callbacksFlatList;
		}

		const result = {};

		Object.keys( this.callbacks ).forEach( ( event ) => {
			if ( ! result[ event ] ) {
				result[ event ] = [];
			}

			Object.keys( this.callbacks[ event ] ).forEach( ( command ) => {
				result[ event ].push( {
					command,
					callbacks: this.callbacks[ event ][ command ],
				} );
			} );
		} );

		return result;
	}

	/**
	 * Function getCurrent();
	 *
	 * Return current command.
	 *
	 * @returns {string}
	 */
	getCurrent() {
		return this.current;
	}

	/**
	 * Function getUsedIds().
	 *
	 * Returns the current used ids.
	 *
	 * @returns {Array}
	 */
	getUsedIds() {
		return this.usedIds;
	}

	/**
	 * Function getCallbacks().
	 *
	 * Get available callbacks for specific event and command.
	 *
	 * @param {string} event
	 * @param {string} command
	 *
	 * @returns {(array|boolean)} callbacks
	 */
	getCallbacks( event, command, args ) {
		const { containers = [ args.container ] } = args,
			containerType = containers[ 0 ] ? containers[ 0 ].type : false;

		let callbacks = [];

		if ( this.callbacks[ event ] && this.callbacks[ event ][ command ] ) {
			if ( containerType && this.callbacks[ event ][ command ][ containerType ] ) {
				callbacks = callbacks.concat( this.callbacks[ event ][ command ][ containerType ] );
			}

			if ( this.callbacks[ event ][ command ].all ) {
				callbacks = callbacks.concat( this.callbacks[ event ][ command ].all );
			}
		}

		if ( callbacks.length ) {
			return callbacks;
		}

		return false;
	}

	/**
	 * function checkEvent().
	 *
	 * Validate if the event is available.
	 *
	 * @param {string} event
	 */
	checkEvent( event ) {
		if ( -1 === Object.keys( this.callbacks ).indexOf( event ) ) {
			throw Error( `${ this.getType() }: '${ event }' is not available.` );
		}
	}

	/**
	 * Function checkInstance().
	 *
	 * Validate given instance.
	 *
	 * @param {HookBase} instance
	 */
	checkInstance( instance ) {
		if ( instance.getType() !== this.getType() ) {
			throw new Error( `invalid instance, please use: 'elementor-api/modules/hook-base.js'. ` );
		}
	}

	/**
	 * Function checkId().
	 *
	 * Validate if the id is not used before.
	 *
	 * @param {string} id
	 */
	checkId( id ) {
		if ( -1 !== this.usedIds.indexOf( id ) ) {
			throw Error( `id: '${ id }' is already in use.` );
		}
	}

	/**
	 * Function shouldRun().
	 *
	 * Determine if the event should run.
	 *
	 * @param {array} callbacks
	 *
	 * @return {boolean}
	 *
	 * @throw {Error}
	 */
	shouldRun( callbacks ) {
		return !! callbacks && callbacks.length;
	}

	/**
	 * Function register().
	 *
	 * Register the callback instance.
	 *
	 * @param {string} event
	 * @param {HookBase} instance
	 *
	 * @returns {{}} Created callback
	 */
	register( event, instance ) {
		const command = instance.getCommand(),
			id = instance.getId(),
			containerType = instance.getContainerType();

		this.checkEvent( event );
		this.checkInstance( instance );
		this.checkId( id );

		return this.registerCallback( id, event, command, instance, containerType );
	}

	/**
	 * Function registerCallback().
	 *
	 * Register callback.
	 *
	 * @param {string} id
	 * @param {string} event
	 * @param {string} command
	 * @param {HookBase} instance
	 * @param {string} containerType
	 *
	 * TODO: Consider replace with typedef.
	 * @returns {{callback: *, id: *, isActive: boolean}}
	 */
	registerCallback( id, event, command, instance, containerType ) {
		if ( ! this.callbacks[ event ][ command ] ) {
			this.callbacks[ event ][ command ] = [];
		}

		// Save used id(s).
		this.usedIds.push( id );

		if ( ! this.callbacks[ event ][ command ] ) {
			this.callbacks[ event ][ command ] = {};
		}

		// TODO: Create HookCallback class/type.
		const callback = {
			id,
			callback: instance.run.bind( instance ),
			isActive: true,

			activate: function() {
				this.isActive = true;
			},
			deactivate: function() {
				this.isActive = false;
			},
		};

		if ( containerType ) {
			if ( ! this.callbacks[ event ][ command ][ containerType ] ) {
				this.callbacks[ event ][ command ][ containerType ] = [];
			}

			this.callbacks[ event ][ command ][ containerType ].push( callback );
		} else {
			if ( ! this.callbacks[ event ][ command ].all ) {
				this.callbacks[ event ][ command ].all = [];
			}

			this.callbacks[ event ][ command ].all.push( callback );
		}

		this.callbacksFlatList[ callback.id ] = callback;

		return callback;
	}

	/**
	 * Function run().
	 *
	 * Run the callbacks.
	 *
	 * @param {string} event
	 * @param {string} command
	 * @param {{}} args
	 * @param {*} result
	 *
	 * @returns {*}
	 */
	run( event, command, args, result = undefined ) {
		const callbacks = this.getCallbacks( event, command, args );

		if ( this.shouldRun( callbacks ) ) {
			this.current = command;

			this.onRun( command, args, event );

			return this.runCallbacks( event, command, callbacks, args, result );
		}

		return false;
	}

	/**
	 * Function runCallbacks().
	 *
	 * Run's the given callbacks.
	 *
	 * @param {string} event
	 * @param {string} command
	 * @param {array} callbacks
	 * @param {{}} args
	 * @param {[]} result
	 */
	runCallbacks( event, command, callbacks, args, result ) {
		const callbacksResult = [];

		for ( const i in callbacks ) {
			const callback = callbacks[ i ];

			if ( ! callback.isActive ) {
				continue;
			}

			// If not exist, set zero.
			if ( undefined === this.depth[ event ][ callback.id ] ) {
				this.depth[ event ][ callback.id ] = 0;
			}

			this.depth[ event ][ callback.id ]++;

			// Prevent recursive hooks.
			if ( 1 === this.depth[ event ][ callback.id ] ) {
				this.onCallback( command, args, event, callback.id );

				try {
					const callbackResult = this.runCallback( event, callback, args, result );

					if ( ! callbackResult ) {
						throw Error( `Callback failed, event: '${ event }'` );
					}

					callbacksResult.push( callbackResult );
				} catch ( e ) {
					// If its 'Hook-Break' then parent `try {}` will handle it.
					if ( e instanceof $e.modules.HookBreak ) {
						throw e;
					}

					elementorCommon.helpers.consoleError( e );
				}
			}

			this.depth[ event ][ callback.id ]--;
		}

		return callbacksResult;
	}

	/**
	 * Function runCallback().
	 *
	 * Run's the given callback.
	 *
	 * @param {string} event
	 * @param {{}} callback
	 * @param {{}} args
	 * @param {*} result
	 *
	 * @returns {*}
	 *
	 * @throw {Error}
	 */
	runCallback( event, callback, args, result ) { // eslint-disable-line no-unused-vars
		elementorModules.forceMethodImplementation();
	}

	/**
	 * Function onRun().
	 *
	 * Called before run a set of callbacks.
	 *
	 * @param {string} command
	 * @param {{}} args
	 * @param {string} event
	 *
	 * @throw {Error}
	 */
	onRun( command, args, event ) { // eslint-disable-line no-unused-vars
		elementorModules.forceMethodImplementation();
	}

	/**
	 * Function onCallback().
	 *
	 * Called before a single callback.
	 *
	 * @param {string} command
	 * @param {{}} args
	 * @param {string} event
	 * @param {string} id
	 *
	 * @throw {Error}
	 */
	onCallback( command, args, event, id ) { // eslint-disable-line no-unused-vars
		elementorModules.forceMethodImplementation();
	}
}
